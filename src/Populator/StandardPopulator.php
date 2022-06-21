<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Populator;

use Doctrine\Common\Util\ClassUtils;
use whatwedo\SearchBundle\Entity\Index;
use whatwedo\SearchBundle\Repository\CustomSearchPopulateQueryBuilderInterface;

class StandardPopulator extends AbstractPopulator
{
    public function index(object $entity)
    {
        if ($this->disableEntityListener) {
            return;
        }

        if ($entity instanceof Index) {
            return;
        }

        if ($this->entityWasIndexed($entity)) {
            return;
        }

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
                    $entry = $this->entityManager->getRepository(Index::class)->findExisting($class, $field, $entity->{$idMethod}());
                    if (! $entry) {
                        $insertData = [];
                        $insertSqlParts = [];
                        $insertData[] = $entity->{$idMethod}();
                        $insertData[] = $class;
                        $insertData[] = $field;
                        $insertData[] = (string) $content;
                        $insertSqlParts[] = '(?,?,?,?)';

                        $this->bulkInsert($insertSqlParts, $insertData);
                    } else {
                        $this->update($entry->{$idMethod}(), $content);
                    }
                }
            }
        }
    }

    /**
     * Populate index of given entity.
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
                        $this->bulkInsert($insertSqlParts, $insertData);
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
            $this->bulkInsert($insertSqlParts, $insertData);
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
}
