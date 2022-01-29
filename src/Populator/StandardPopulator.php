<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Populator;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use whatwedo\CoreBundle\Manager\FormatterManager;
use whatwedo\SearchBundle\Manager\IndexManager;
use whatwedo\SearchBundle\Repository\CustomSearchPopulateQueryBuilderInterface;

class StandardPopulator implements PopulatorInterface
{
    private PopulateOutputInterface $output;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected IndexManager $indexManager,
        protected FormatterManager $formatterManager
    ) {
        $entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->output = new NullPopulateOutput();
    }

    public function populate(?PopulateOutputInterface $output = null, ?string $entityClasses = null): void
    {
        if ($output) {
            $this->output = $output;
        }

        $entities = $this->indexManager->getIndexedEntities();

        // for example disable unwanted EventListeners
        $this->prePopulate();

        // Flush index
        $this->output->log('Flushing index table');
        $this->indexManager->flush();

        $entityExists = $this->entityManager->getMetadataFactory()->isTransient($entityClasses);
        if (! $entityExists) {
            $this->output->log('Entity "' . $entityClasses . '" not a valid Doctrine entity!');

            return;
        }

        if ($entityClasses && ! \in_array(str_replace('\\\\', '\\', $entityClasses), $entities, true)) {
            $this->output->log('Entity "' . $entityClasses . '" not a indexed entity!');

            return;
        }

        // Indexing entities
        $runned = false;
        foreach ($entities as $entityName) {
            if ($entityClasses && $entityName !== str_replace('\\\\', '\\', $entityClasses)) {
                continue;
            }
            $this->indexEntity($entityName);
            $runned = true;
        }

        if (! $runned) {
            $this->output->log('Indexer not runned!');
        }
    }

    protected function prePopulate()
    {
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
        $entityClass = new \ReflectionClass($entityName);
        if ($entityClass->isAbstract()) {
            return;
        }

        /** @var Connection $connection */
        $connection = $this->entityManager->getConnection();
        $this->output->log('Indexing of entity ' . $entityName);

        // Get required meta information
        $indexes = $this->indexManager->getIndexesOfEntity($entityName);
        $idMethod = $this->indexManager->getIdMethod($entityName);

        $repository = $this->entityManager->getRepository($entityName);

        if ($repository instanceof CustomSearchPopulateQueryBuilderInterface) {
            $queryBuilder = $repository->getCustomSearchPopulateQueryBuilder();
        } else {
            // get clean QueryBuilder
            $queryBuilder = $this->entityManager->createQueryBuilder();
            $queryBuilder->from($entityName, 'e')->select('e');
        }

        $entities = $queryBuilder->getQuery()->iterate();
        if ($repository instanceof CustomSearchPopulateQueryBuilderInterface) {
            $entityCount = $repository->customSearchPopulateCount();
        } else {
            $entityCount = $this->entityManager->getRepository($entityName)->count([]);
        }

        $this->output->progressStart($entityCount * count($indexes));

        $i = 0;

        $insertData = [];
        $insertSqlParts = [];

        foreach ($entities as $entity) {
            /** @var \whatwedo\SearchBundle\Annotation\Index $index */
            foreach ($indexes as $field => $index) {
                $fieldMethod = $this->indexManager->getFieldAccessorMethod($entityName, $field);

                $formatter = $this->formatterManager->getFormatter($index->getFormatter());
                $formatter->processOptions($index->getFormatterOptions());
                $content = $formatter->getString($entity[0]->{$fieldMethod}());

                // Persist entry
                if (! empty($content)) {
                    $insertData[] = $entity[0]->{$idMethod}();
                    $insertData[] = $entityName;
                    $insertData[] = $field;
                    $insertData[] = (string) $content;
                    $insertSqlParts[] = '(?,?,?,?)';
                }

                // Update progress bar every 200 iterations
                // as well as gc
                if ($i % 200 === 0) {
                    if (count($insertData)) {
                        $this->bulkInsert($insertSqlParts, $insertData, $connection);
                    }
                    $insertSqlParts = [];
                    $insertData = [];

                    $this->output->setProgress($i);
//                    $progress->setProgress($i);
                    $this->gc();
                }
                ++$i;
            }
        }

        if (count($insertData)) {
            $this->bulkInsert($insertSqlParts, $insertData, $connection);
        }

        $this->gc();

        // Tear down progress bar
        $this->output->progressFinish();
//        $progress->finish();
        $this->output->log(PHP_EOL);
    }

    /**
     * Clean up garbage.
     */
    protected function gc()
    {
        $this->entityManager->clear();
        gc_collect_cycles();
    }

    protected function escape(string $value): string
    {
        if (mb_strpos($value, '\\\\') === false) {
            $value = str_replace('\\', '\\\\', $value);
        }

        return $value;
    }

    private function bulkInsert(array $insertSqlParts, array $insertData, \Doctrine\DBAL\Connection $connection)
    {
        $bulkInsertStatetment = $connection->prepare('INSERT INTO whatwedo_search_index (foreign_id, model, field, content) VALUES ' . implode(',', $insertSqlParts));
        $bulkInsertStatetment->executeStatement($insertData);
    }
}