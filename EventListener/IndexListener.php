<?php
/**
 * Copyright (c) 2016, whatwedo GmbH
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

namespace whatwedo\SearchBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Statement;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use whatwedo\CoreBundle\Manager\FormatterManager;
use whatwedo\SearchBundle\Entity\Index;
use whatwedo\SearchBundle\Manager\IndexManager;

class IndexListener implements EventSubscriber
{
    /**
     * @var IndexManager
     */
    protected $indexManager;

    /**
     * @var Statement
     */
    protected $indexInsertStmt;

    /**
     * @var Statement
     */
    protected $indexUpdateStmt;

    /**
     * @var FormatterManager
     */
    protected $formatterManager;

    /**
     * Prevent infinite recursion
     * @var array
     */
    protected static $indexVisited = [];

    /**
     * Prevent infinite recursion
     * @var array
     */
    protected static $removeVisited = [];

    /**
     * IndexListener constructor.
     */
    public function __construct(IndexManager $indexManager, FormatterManager $formatterManager)
    {
        $this->indexManager = $indexManager;
        $this->formatterManager = $formatterManager;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'postPersist',
            'postUpdate',
            'preRemove',
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->index($args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->index($args);
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $em = $args->getObjectManager();
        $entity = $args->getObject();
        if ($entity instanceof Index) {
            return;
        }
        $oid = spl_object_hash($entity);
        if (isset(static::$removeVisited[$oid])) {
            return;
        }
        static::$removeVisited[$oid] = true;
        $entityName = \get_class($entity);
        if (!$this->indexManager->hasEntityIndexes($entityName)) {
            return;
        }
        $classes = $this->getClassTree($entityName);
        foreach ($classes as $class) {
            if (!$em->getMetadataFactory()->hasMetadataFor($class)
                || !$this->indexManager->hasEntityIndexes($class)) {
                continue;
            }
            $indexes = $this->indexManager->getIndexesOfEntity($class);
            $idMethod = $this->indexManager->getIdMethod($entityName);
            foreach (array_keys($indexes) as $field) {
                $entry = $em->getRepository(Index::class)->findExisting($class, $field, $entity->{$idMethod}());
                if (null !== $entry) {
                    $em->remove($entry);
                }
            }
        }

        $em->flush();
    }

    public function index(LifecycleEventArgs $args)
    {
        $em = $args->getObjectManager();
        if (null === $this->indexInsertStmt) {
            $indexPersister = $em->getUnitOfWork()->getEntityPersister(Index::class);
            $rmIndexInsertSQL = new \ReflectionMethod($indexPersister, 'getInsertSQL');
            $rmIndexInsertSQL->setAccessible(true);
            $this->indexInsertStmt = $em->getConnection()->prepare($rmIndexInsertSQL->invoke($indexPersister));
            $this->indexUpdateStmt = $em->getConnection()->prepare(
                $em->createQueryBuilder()
                    ->update(Index::class, 'i')
                    ->set('i.content', '?1')
                    ->where('i.id = ?2')
                    ->getQuery()
                    ->getSql()
            );
        }
        $entity = $args->getObject();
        if ($entity instanceof Index) {
            return;
        }
        $oid = spl_object_hash($entity);
        if (isset(static::$indexVisited[$oid])) {
            return;
        }
        static::$indexVisited[$oid] = true;
        $entityName = \get_class($entity);
        if (!$this->indexManager->hasEntityIndexes($entityName)) {
            return;
        }

        $classes = $this->getClassTree($entityName);
        foreach ($classes as $class) {
            if (!$em->getMetadataFactory()->hasMetadataFor($class)
                || !$this->indexManager->hasEntityIndexes($class)) {
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
                if (!empty($content)) {
                    $entry = $em->getRepository(Index::class)->findExisting($class, $field, $entity->{$idMethod}());
                    if (!$entry) {
                        $this->indexInsertStmt->bindValue(1, $entity->{$idMethod}());
                        $this->indexInsertStmt->bindValue(2, $class);
                        $this->indexInsertStmt->bindValue(3, $field);
                        $this->indexInsertStmt->bindValue(4, $content);
                        $this->indexInsertStmt->execute();
                    } else {
                        $this->indexUpdateStmt->bindValue(1, $content);
                        $this->indexUpdateStmt->bindValue(2, $entry->getId());
                        $this->indexUpdateStmt->execute();
                    }
                }
            }
        }
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
}
