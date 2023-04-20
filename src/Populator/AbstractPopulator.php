<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Populator;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use whatwedo\CoreBundle\Manager\FormatterManager;
use whatwedo\SearchBundle\Exception\ClassNotDoctrineMappedException;
use whatwedo\SearchBundle\Exception\ClassNotIndexedEntityException;
use whatwedo\SearchBundle\Manager\IndexManager;
use whatwedo\SearchBundle\Repository\CustomSearchPopulateQueryBuilderInterface;

abstract class AbstractPopulator implements PopulatorInterface
{
    protected array $indexVisited = [];

    protected array $removeVisited = [];

    protected PopulateOutputInterface $output;

    protected bool $disableEntityListener = false;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected IndexManager $indexManager,
        protected FormatterManager $formatterManager
    ) {
        $this->output = new NullPopulateOutput();
    }

    public function populate(?PopulateOutputInterface $output = null, ?string $entityClass = null): void
    {
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        if ($this->disableEntityListener) {
            return;
        }
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

    public function remove(object $entity): void
    {
        if ($this->entityWasRemoved($entity)) {
            return;
        }

        if ($this->disableEntityListener) {
            return;
        }

        $entityName = ClassUtils::getClass($entity);
        if (! $this->indexManager->hasEntityIndexes($entityName)) {
            return;
        }
        $classes = $this->getClassTree($entityName);
        foreach ($classes as $class) {
            if (! $this->canBeIndexed($class)) {
                continue;
            }
            $idMethod = $this->indexManager->getIdMethod($entityName);
            $this->delete((string) $entity->{$idMethod}(), $class);
        }
    }

    public function disableEntityListener(bool $disable)
    {
        $this->disableEntityListener = $disable;
    }

    public function resetVisited(): void
    {
        $this->removeVisited = [];
        $this->indexVisited = [];
    }

    protected function canBeIndexed(string $class): bool
    {
        if (! $this->entityManager->getMetadataFactory()->hasMetadataFor($class)) {
            return false;
        }
        $metadata = $this->entityManager->getClassMetadata($class);
        if (! $this->indexManager->hasEntityIndexes($class) || $metadata->isMappedSuperclass) {
            return false;
        }
        return true;
    }

    protected function getIndexEntityWorkingValues(string $entityName): array|false
    {
        $entityClass = new \ReflectionClass($entityName);
        if ($entityClass->isAbstract()) {
            return false;
        }

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

        return [$entities, $idMethod, $indexes];
    }

    abstract protected function indexEntity($entityName);

    protected function prePopulate()
    {
    }

    protected function bulkInsert(array $insertSqlParts, array $insertData)
    {
        $connection = $this->entityManager->getConnection();
        $bulkInsertStatetment = $connection->prepare('INSERT INTO whatwedo_search_index (foreign_id, model, grp, content) VALUES ' . implode(',', $insertSqlParts));
        $bulkInsertStatetment->executeStatement($insertData);
    }

    protected function update(string $id, string $content)
    {
        $connection = $this->entityManager->getConnection();
        $updateStatement = $connection->prepare('UPDATE whatwedo_search_index SET content=? WHERE id=?');
        $updateStatement->executeStatement([$content, $id]);
    }

    protected function delete(string $foreignId, string $model)
    {
        $connection = $this->entityManager->getConnection();
        $updateStatement = $connection->prepare('DELETE FROM whatwedo_search_index WHERE foreign_id=? and model=?');
        $updateStatement->executeStatement([$foreignId, $model]);
    }

    protected function entityWasIndexed(object $entity): bool
    {
        $oid = spl_object_hash($entity);
        if (isset($this->indexVisited[$oid])) {
            return true;
        }
        $this->indexVisited[$oid] = true;

        return false;
    }

    protected function entityWasRemoved(object $entity): bool
    {
        $oid = spl_object_hash($entity);
        if (isset($this->removeVisited[$oid])) {
            return true;
        }
        $this->removeVisited[$oid] = true;

        return false;
    }

    protected function getClassTree($entityFqcn): array
    {
        $classes = class_parents($entityFqcn);
        array_unshift($classes, $entityFqcn);

        return $classes;
    }
}
