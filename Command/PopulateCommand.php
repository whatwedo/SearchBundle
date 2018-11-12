<?php
/**
 * Copyright (c) 2017, whatwedo GmbH
 * All rights reserved
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
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use whatwedo\CoreBundle\Command\BaseCommand;
use whatwedo\CoreBundle\Formatter\FormatterInterface;
use whatwedo\CoreBundle\Manager\FormatterManager;
use whatwedo\SearchBundle\Entity\Index;
use whatwedo\SearchBundle\Manager\IndexManager;

/**
 * Class PopulateCommand
 * @package whatwedo\SearchBundle\Command
 */
class PopulateCommand extends BaseCommand
{

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var RegistryInterface
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
     * @param RegistryInterface $doctrine
     * @param IndexManager $indexManager
     * @param FormatterManager $formatterManager
     */
    public function __construct(RegistryInterface $doctrine, IndexManager $indexManager, FormatterManager $formatterManager)
    {
        parent::__construct(null);

        $this->doctrine = $doctrine;
        $this->indexManager = $indexManager;
        $this->formatterManager = $formatterManager;
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('whatwedo:search:populate')
            ->setDescription('Populate the search index')
            ->setHelp('This command populate the search index according to the entity annotations')
            ->addArgument('entity', InputArgument::OPTIONAL, 'Only populate index for this entity');;
    }

    protected function prePopulate()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Initialize command
        parent::execute($input, $output);
        $this->em = $this->doctrine->getManager();
        $entities = $this->indexManager->getIndexedEntities();

        // Disable SQL logging
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        // for example disable unwanted EventListeners
        $this->prePopulate();

        // Start transaction
        $this->debug('Starting SQL transaction');
        $this->em->beginTransaction();

        // Flush index
        $this->log('Flushing index table');
        $this->indexManager->flush();

        $targetEntity = $input->getArgument('entity');

        // Indexing entities
        foreach ($entities as $entityName) {
            if($targetEntity && $entityName != $targetEntity) continue;
            $this->indexEntity($entityName);
        }

        // Commit transaction
        $this->debug('Committing SQL transaction');
        $this->em->commit();

        // Tear down
        $this->tearDown();
    }

    /**
     * Populate index of given entity
     *
     * @param $entityName
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

        // Get entities
        $entities = $this->em->getRepository($entityName)->findAll();

        // Initialize progress bar
        $progress = new ProgressBar($this->output, count($entities) * count($indexes));
        $progress->start();

        $i = 0;
        /** @var \whatwedo\SearchBundle\Annotation\Index $index */
        foreach ($indexes as $field => $index) {
            $fieldMethod = $this->indexManager->getFieldAccessorMethod($entityName, $field);
            foreach ($entities as $entity) {

                // Get content
                $formatter = $this->formatterManager->getFormatter($index->getFormatter());
                $formatter->processOptions($index->getFormatterOptions());
                $content = $formatter->getString($entity->$fieldMethod());

                // Persist entry
                if (!empty($content)) {
                    $entry = new Index();
                    $entry->setModel($entityName)
                        ->setForeignId($entity->$idMethod())
                        ->setField($field)
                        ->setContent($content);
                    $this->em->persist($entry);
                    $this->em->flush($entry);
                }

                // Update progress bar every 200 iterations
                // as well as gc
                if ($i % 200 == 0) {
                    $progress->setProgress($i);
                    $this->gc();
                }
                $i ++;
            }
        }
        $this->gc();

        // Tear down progress bar
        $progress->finish();
        $this->output->write(PHP_EOL);
    }

    /**
     * Clean up garbage
     */
    protected function gc()
    {
        $this->em->clear();
        gc_collect_cycles();
    }
}
