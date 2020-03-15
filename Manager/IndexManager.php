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

namespace whatwedo\SearchBundle\Manager;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\Mapping\ClassMetadata;
use whatwedo\SearchBundle\Annotation\Index;
use whatwedo\SearchBundle\Exception\MethodNotFoundException;

/**
 * Class IndexManager.
 */
class IndexManager
{
    /**
     * @var \Doctrine\Persistence\ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * IndexManager constructor.
     */
    public function __construct(\Doctrine\Persistence\ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Flush index table.
     */
    public function flush()
    {
        $connection = $this->getEntityManager()->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $tableName = $this->getEntityManager()->getClassMetadata('whatwedoSearchBundle:Index')->getTableName();
        if ('mysql' === $connection->getDatabasePlatform()->getName()) {
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
        }
        $query = $dbPlatform->getTruncateTableSql($tableName);
        $connection->executeUpdate($query);
        if ('mysql' === $connection->getDatabasePlatform()->getName()) {
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /**
     * Get indexes of given entity.
     *
     * @param $entity
     *
     * @return array
     */
    public function getIndexesOfEntity($entity)
    {
        $fields = [];
        $reflection = new \ReflectionClass($entity);
        $annotationReader = new AnnotationReader();
        foreach ($reflection->getProperties() as $property) {
            $annotation = $annotationReader->getPropertyAnnotation($property, Index::class);
            if (null !== $annotation) {
                $fields[$property->getName()] = $annotation;
            }
        }
        foreach ($reflection->getMethods() as $method) {
            $annotation = $annotationReader->getMethodAnnotation($method, Index::class);
            if (null !== $annotation) {
                $fields[$method->getName()] = $annotation;
            }
        }

        // Check if entitiess exists
        if (isset($this->config['entities'])) {
            foreach ($this->config['entities'] as $entityConfig) {
                if ($entityConfig['class'] === $entity) {
                    foreach ($entityConfig['fields'] as $fieldConfig) {
                        $annotation = new Index();
                        if (isset($fieldConfig['formatter'])) {
                            $annotation->setFormatter($fieldConfig['formatter']);
                        }
                        $fields[$fieldConfig['name']] = $annotation;
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * Return true if there are at least one index in the
     * given entity.
     *
     * @param $entity
     *
     * @return bool
     */
    public function hasEntityIndexes($entity)
    {
        $indexes = $this->getIndexesOfEntity($entity);
        if (\count($indexes) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Get all entities with any defined index.
     *
     * @return array
     */
    public function getIndexedEntities()
    {
        $tables = [];
        $metaTables = $this->getEntityManager()->getMetadataFactory()->getAllMetadata();
        /** @var ClassMetadata $metaTable */
        foreach ($metaTables as $metaTable) {
            $entity = $metaTable->getName();
            if ($this->hasEntityIndexes($entity)) {
                $tables[] = $entity;
            }
        }

        return $tables;
    }

    /**
     * Get id method.
     *
     * @param $entityName
     *
     * @return string
     */
    public function getIdMethod($entityName)
    {
        $field = $this->getEntityManager()->getClassMetadata($entityName)->getSingleIdentifierFieldName();

        return $this->getFieldAccessorMethod($entityName, $field);
    }

    /**
     * Get field accessor method.
     *
     * @param $entityName
     * @param $field
     *
     * @throws MethodNotFoundException
     *
     * @return string
     */
    public function getFieldAccessorMethod($entityName, $field)
    {
        $prefixes = [
            'get',
            'is',
            'has',
        ];
        if (method_exists($entityName, $field)) {
            return $field;
        }
        foreach ($prefixes as $prefix) {
            $method = $prefix.ucfirst($field);
            if (method_exists($entityName, $method)) {
                return $method;
            }
        }
        throw new MethodNotFoundException('Accessor method of field '.$field.' of entity '.$entityName.' not found');
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     *
     * @return IndexManager
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->doctrine->getManager();
    }
}
