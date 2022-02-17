<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Populator;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use whatwedo\CoreBundle\Manager\FormatterManager;
use whatwedo\SearchBundle\Entity\Index;
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
        $entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->output = new NullPopulateOutput();
    }



    protected function bulkInsert(array $insertSqlParts, array $insertData)
    {
        $connection = $this->entityManager->getConnection();
        $bulkInsertStatetment = $connection->prepare('INSERT INTO whatwedo_search_index (foreign_id, model, field, content) VALUES ' . implode(',', $insertSqlParts));
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


    public function disableEntityListener(bool $disable)
    {
        $this->disableEntityListener = $disable;
    }


    protected function entityWasIndexed(object $entity): bool {
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

    public function resetVisited(): void {
        $this->removeVisited = [];
        $this->indexVisited = [];
    }

    protected function getClassTree($entityFqcn): array
    {
        $classes = class_parents($entityFqcn);
        array_unshift($classes, $entityFqcn);

        return $classes;
    }
}
