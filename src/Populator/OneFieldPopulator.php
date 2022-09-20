<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Populator;

use Doctrine\Common\Util\ClassUtils;
use whatwedo\SearchBundle\Entity\Index;

class OneFieldPopulator extends AbstractPopulator
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

            $idMethod = $this->indexManager->getIdMethod($class);

            /** @var \whatwedo\SearchBundle\Annotation\Index $index */
            $groupedContent = $this->collectEntityIndexData($entityName, $entity);

            foreach ($groupedContent as $group => $content) {
                $entry = $this->entityManager->getRepository(Index::class)->findExisting($class, $group, $entity->{$idMethod}());
                if (! $entry) {
                    $insertData = [];
                    $insertSqlParts = [];
                    $insertData[] = $entity->{$idMethod}();
                    $insertData[] = $class;
                    $insertData[] = $group;
                    $insertData[] = implode(' ', $content);
                    $insertSqlParts[] = '(?,?,?,?)';

                    $this->bulkInsert($insertSqlParts, $insertData);
                } else {
                    $this->update($entry->{$idMethod}(), implode(' ', $content));
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
        $workingValues = $this->getIndexEntityWorkingValues($entityName);
        if ($workingValues === false) {
            return;
        }
        [$entities, $idMethod] = $workingValues;

        $i = 0;
        $insertData = [];
        $insertSqlParts = [];

        foreach ($entities as $entity) {
            $groupedContent = $this->collectEntityIndexData($entityName, $entity[0]);

            // Persist entry
            foreach ($groupedContent as $group => $content) {
                $insertData[] = $entity[0]->{$idMethod}();
                $insertData[] = $entityName;
                $insertData[] = $group;
                $insertData[] = implode(' ', $content);
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

    protected function collectEntityIndexData($entityName, $entity): array
    {
        $indexes = $this->indexManager->getIndexesOfEntity($entityName);

        $content = [];
        /** @var \whatwedo\SearchBundle\Annotation\Index $index */
        foreach ($indexes as $field => $index) {
            $fieldMethod = $this->indexManager->getFieldAccessorMethod($entityName, $field);

            $formatter = $this->formatterManager->getFormatter($index->getFormatter());
            $formatter->processOptions($index->getFormatterOptions());
            foreach ($index->getGroups() as $indexGroup) {
                $content[$indexGroup][] = $formatter->getString($entity->{$fieldMethod}());
            }
        }

        return $content;
    }
}
