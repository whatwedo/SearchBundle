<?php
/**
 * Copyright (c) 2017, whatwedo GmbH
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace whatwedo\SearchBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use whatwedo\CoreBundle\Command\BaseCommand;
use whatwedo\CoreBundle\Manager\FormatterManager;
use whatwedo\SearchBundle\Entity\Index;
use whatwedo\SearchBundle\Manager\IndexManager;

/**
 * Class PopulateCommand.
 */
class PopulateCommand extends BaseCommand
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var IndexManager
     */
    protected $indexManager;

    /**
     * @var FormatterManager
     */
    protected $formatterManager;

    /**
     * PopulateCommand constructor.
     */
    public function __construct(ManagerRegistry $doctrine, IndexManager $indexManager, FormatterManager $formatterManager)
    {
        parent::__construct(null);

        $this->doctrine = $doctrine;
        $this->indexManager = $indexManager;
        $this->formatterManager = $formatterManager;
    }

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this
            ->setName('whatwedo:search:populate')
            ->setDescription('Populate the search index')
            ->setHelp('This command populate the search index according to the entity annotations')
            ->addArgument('entity', InputArgument::OPTIONAL, 'Only populate index for this entity');
    }

    protected function prePopulate()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Initialize command
        parent::execute($input, $output);
        $this->em = $this->doctrine->getManager();
        $entities = $this->indexManager->getIndexedEntities();

        // Disable SQL logging
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        // for example disable unwanted EventListeners
        $this->prePopulate();

        // Flush index
        $this->log('Flushing index table');
        $this->indexManager->flush();

        $targetEntity = $this->escape($input->getArgument('entity'));

        $entityExists = $this->doctrine->getManager()->getMetadataFactory()->isTransient($targetEntity);
        if (!$entityExists) {
            $this->log('Entity "'.$targetEntity.'" not a valid Doctrine entity!');

            return 1;
        }

        if ($targetEntity && !\in_array(str_replace('\\\\', '\\', $targetEntity), $entities, true)) {
            $this->log('Entity "'.$targetEntity.'" not a indexed entity!');

            return 1;
        }

        // Indexing entities
        $runned = false;
        foreach ($entities as $entityName) {
            if ($targetEntity && $entityName !== str_replace('\\\\', '\\', $targetEntity)) {
                continue;
            }
            $this->indexEntity($entityName);
            $runned = true;
        }

        if (!$runned) {
            $this->log('Indexer not runned!');
        }

        // Tear down
        $this->tearDown();

        return 0;
    }

    /**
     * Populate index of given entity.
     *
     * @param $entityName
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \whatwedo\SearchBundle\Exception\MethodNotFoundException
     */
    protected function indexEntity($entityName)
    {
        $this->log('Indexing of entity '.$entityName);

        // Get required meta information
        $indexes = $this->indexManager->getIndexesOfEntity($entityName);
        $idMethod = $this->indexManager->getIdMethod($entityName);

        // get clean QueryBuilder
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder->from($entityName, 'e')->select('e');

        // Get entities
        $entities = $queryBuilder->getQuery()->iterate();
        $entityCount = $this->em->getRepository($entityName)->count([]);

        // Initialize progress bar
        $progress = new ProgressBar($this->output, $entityCount * \count($indexes));
        $progress->start();

        $indexQuery = $this->em->createQueryBuilder()
            ->from(Index::class, 'e')
            ->select('e')
            ->where('e.model = :model')
            ->andWhere('e.foreignId = :foreignId')
            ->andWhere('e.field = :field')
            ->setMaxResults(1)
        ;

        $i = 0;
        foreach ($entities as $entity) {
            /** @var \whatwedo\SearchBundle\Annotation\Index $index */
            foreach ($indexes as $field => $index) {
                $fieldMethod = $this->indexManager->getFieldAccessorMethod($entityName, $field);

                // Get content
                $formatter = $this->formatterManager->getFormatter($index->getFormatter());
                if (method_exists($formatter, 'processOptions')) {
                    $formatter->processOptions($index->getFormatterOptions());
                }
                $content = $formatter->getString($entity[0]->$fieldMethod());

                // Persist entry
                if (!empty($content)) {
                    $entry = $indexQuery->setParameters([
                        'model' => $entityName,
                        'foreignId' => $entity[0]->$idMethod(),
                        'field' => $field,
                    ])->getQuery()->getOneOrNullResult();

                    if (!$entry) {
                        $entry = new Index();
                    }

                    $entry->setModel($entityName)
                        ->setForeignId($entity[0]->$idMethod())
                        ->setField($field)
                        ->setContent($content);

                    if (!$entry->getId()) {
                        $this->em->persist($entry);
                    }
                    $this->em->flush($entry);
                }

                // Update progress bar every 200 iterations
                // as well as gc
                if (0 === $i % 200) {
                    $progress->setProgress($i);
                    $this->gc();
                }
                ++$i;
            }
            $this->em->detach($entity[0]);
        }
        $this->gc();

        // Tear down progress bar
        $progress->finish();
        $this->output->write(PHP_EOL);
    }

    /**
     * Clean up garbage.
     */
    protected function gc()
    {
        $this->em->clear();
        gc_collect_cycles();
    }

    protected function escape(?string $value): ?string
    {
        if (false === mb_strpos($value, '\\\\')) {
            $value = str_replace('\\', '\\\\', $value);
        }

        return $value;
    }
}
