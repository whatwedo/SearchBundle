<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Populator;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManagerInterface;
use whatwedo\CoreBundle\Manager\FormatterManager;
use whatwedo\SearchBundle\Entity\Index;
use whatwedo\SearchBundle\Exception\ClassNotDoctrineMappedException;
use whatwedo\SearchBundle\Exception\ClassNotIndexedEntityException;
use whatwedo\SearchBundle\Manager\IndexManager;
use whatwedo\SearchBundle\Repository\CustomSearchPopulateQueryBuilderInterface;

class StandardPopulator implements PopulatorInterface
{
    protected ?Statement $indexInsertStmt = null;

    protected ?Statement $indexUpdateStmt = null;

    protected static array $indexVisited = [];

    protected static array $removeVisited = [];

    private PopulateOutputInterface $output;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected IndexManager $indexManager,
        protected FormatterManager $formatterManager
    ) {
        $entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->output = new NullPopulateOutput();
    }

    public function populate(?PopulateOutputInterface $output = null, ?string $entityClass = null): void
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

        if ($entityClass) {
            $entityExists = $this->entityManager->getMetadataFactory()->isTransient($entityClass);
            if ($entityExists) {
                throw new ClassNotDoctrineMappedException($entityClass);
            }

            if ($entityClass && ! \in_array($entityClass, $entities, true)) {
                throw new ClassNotIndexedEntityException($entityClass);
            }
        }

        $this->output->log(sprintf('Index %s entites', count($entities)));
        foreach ($entities as $entityName) {
            if ($entityClass && $entityName !== str_replace('\\\\', '\\', $entityClass)) {
                continue;
            }
            $this->indexEntity($entityName);
        }
    }

    public function index(object $entity)
    {
        if ($this->indexInsertStmt === null) {
            $indexPersister = $this->entityManager->getUnitOfWork()->getEntityPersister(Index::class);
            $rmIndexInsertSQL = new \ReflectionMethod($indexPersister, 'getInsertSQL');
            $rmIndexInsertSQL->setAccessible(true);
            $this->indexInsertStmt = $this->entityManager->getConnection()->prepare($rmIndexInsertSQL->invoke($indexPersister));
            $this->indexUpdateStmt = $this->entityManager->getConnection()->prepare(
                $this->entityManager->createQueryBuilder()
                    ->update(Index::class, 'i')
                    ->set('i.content', '?1')
                    ->where('i.id = ?2')
                    ->getQuery()
                    ->getSql()
            );
        }
        if ($entity instanceof Index) {
            return;
        }
        $oid = spl_object_hash($entity);
        if (isset(static::$indexVisited[$oid])) {
            return;
        }
        static::$indexVisited[$oid] = true;
        $entityName = ClassUtils::getClass($entity);
        if (! $this->indexManager->hasEntityIndexes($entityName)) {
            return;
        }

        $classes = $this->getClassTree($entityName);
        foreach ($classes as $class) {
            if (! $this->entityManager->getMetadataFactory()->hasMetadataFor($class)
                || ! $this->indexManager->hasEntityIndexes($class)) {
                continue;
            }

            $indexes = $this->indexManager->getIndexesOfEntity($class);
            $idMethod = $this->indexManager->getIdMethod($class);

            /** @var \whatwedo\SearchBundle\Annotation\Index $index */
            foreach ($indexes as $field => $index) {
                $fieldMethod = $this->indexManager->getFieldAccessorMethod($class, $field);
                $formatter = $this->formatterManager->getFormatter($index->getFormatter());
                if (method_exists($formatter, 'processOptions')) {
                    $formatter->processOptions($index->getFormatterOptions());
                }
                $content = $formatter->getString($entity->{$fieldMethod}());
                if (! empty($content)) {
                    $entry = $this->entityManager->getRepository('whatwedoSearchBundle:Index')->findExisting($class, $field, $entity->{$idMethod}());
                    if (! $entry) {
                        $this->indexInsertStmt->bindValue(1, $entity->{$idMethod}());
                        $this->indexInsertStmt->bindValue(2, $class);
                        $this->indexInsertStmt->bindValue(3, $field);
                        $this->indexInsertStmt->bindValue(4, $content);
                        $this->indexInsertStmt->executeStatement();
                    } else {
                        $this->indexUpdateStmt->bindValue(1, $content);
                        $this->indexUpdateStmt->bindValue(2, $entry->getId());
                        $this->indexUpdateStmt->executeStatement();
                    }
                }
            }
        }
    }

    public function remove(object $entity): void
    {
        if ($entity instanceof Index) {
            return;
        }
        $oid = spl_object_hash($entity);
        if (isset(static::$removeVisited[$oid])) {
            return;
        }
        static::$removeVisited[$oid] = true;
        $entityName = \get_class($entity);
        if (! $this->indexManager->hasEntityIndexes($entityName)) {
            return;
        }
        $classes = $this->getClassTree($entityName);
        foreach ($classes as $class) {
            if (! $this->entityManager->getMetadataFactory()->hasMetadataFor($class)
                || ! $this->indexManager->hasEntityIndexes($class)) {
                continue;
            }
            $indexes = $this->indexManager->getIndexesOfEntity($class);
            $idMethod = $this->indexManager->getIdMethod($entityName);
            foreach (array_keys($indexes) as $field) {
                $entry = $this->entityManager->getRepository(Index::class)->findExisting($class, $field, $entity->{$idMethod}());
                if ($entry !== null) {
                    $this->entityManager->remove($entry);
                }
            }
        }

        $this->entityManager->flush();
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
                    $this->gc();
                }
                ++$i;
            }
        }

        if (count($insertData)) {
            $this->bulkInsert($insertSqlParts, $insertData, $connection);
        }

        $this->gc();

        $this->output->progressFinish();
    }

    /**
     * Clean up garbage.
     */
    protected function gc()
    {
        $this->entityManager->clear();
        gc_collect_cycles();
    }

    /**
     * Get class tree.
     *
     * @param $className
     *
     * @return array
     */
    protected function getClassTree($className)
    {
        $classes = class_parents($className);
        array_unshift($classes, $className);

        return $classes;
    }

    private function bulkInsert(array $insertSqlParts, array $insertData, \Doctrine\DBAL\Connection $connection)
    {
        $bulkInsertStatetment = $connection->prepare('INSERT INTO whatwedo_search_index (foreign_id, model, field, content) VALUES ' . implode(',', $insertSqlParts));
        $bulkInsertStatetment->executeStatement($insertData);
    }
}
