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

namespace whatwedo\SearchBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Annotations\AnnotationReader;
use whatwedo\SearchBundle\Annotation\Searchable;
use whatwedo\SearchBundle\Entity\Index;
use whatwedo\SearchBundle\Entity\PostSearchInterface;
use whatwedo\SearchBundle\Entity\PreSearchInterface;

/**
 * Class IndexRepository.
 */
class IndexRepository extends ServiceEntityRepository
{
    public function __construct(\Doctrine\Persistence\ManagerRegistry $registry)
    {
        parent::__construct($registry, Index::class);
    }

    /**
     * @param $query
     * @param string|null $entity
     * @param string|null $field
     *
     * @return array
     */
    public function search($query, $entity = null, $field = null)
    {
        $qb = $this->createQueryBuilder('i')
            ->select('i.foreignId')
            ->addSelect('MATCH_AGAINST(i.content, :query) AS _matchQuote')
            ->where('MATCH_AGAINST(i.content, :query) > :minScore')
            ->orWhere('i.content LIKE :queryWildcard')
            ->groupBy('i.foreignId')
            ->addGroupBy('_matchQuote')
            ->addOrderBy('_matchQuote', 'DESC')
            ->setParameter('query', $query)
            ->setParameter('queryWildcard', '%'.$query.'%')
            ->setParameter('minScore', round(\mb_strlen($query) * 0.8));

        if ($entity) {
            $qb->andWhere('i.model = :entity')
                ->setParameter('entity', $entity);
        }

        if ($field) {
            $qb->andWhere('i.field = :fieldName')
                ->setParameter('fieldName', $field);
        }

        if ($entity) {
            // preSearch
            $reflection = new \ReflectionClass($entity);
            $annotationReader = new AnnotationReader();

            /** @var Searchable $searchableAnnotations */
            $searchableAnnotations = $annotationReader->getClassAnnotation($reflection, Searchable::class);

            if ($searchableAnnotations) {
                if ($class = $searchableAnnotations->getPreSearch()) {
                    if (class_exists($class)) {
                        $reflection = new \ReflectionClass($class);
                        if ($reflection->implementsInterface(PreSearchInterface::class)) {
                            (new $class())->preSearch($qb, $query, $entity, $field);
                        }
                    }
                }
            }
        }

        $result = $qb->getQuery()->getScalarResult();

        if ($entity) {
            // postSearch
            if ($searchableAnnotations) {
                if ($class = $searchableAnnotations->getPostSearch()) {
                    if (class_exists($class)) {
                        $reflection = new \ReflectionClass($class);
                        if ($reflection->implementsInterface(PostSearchInterface::class)) {
                            $result = (new $class())->postSearch($result, $query, $entity, $field);
                        }
                    }
                }
            }
        }

        $ids = [];
        foreach ($result as $row) {
            $ids[] = $row['foreignId'];
        }

        return $ids;
    }

    /**
     * @param $entity
     * @param $field
     * @param $foreignId
     *
     * @return Index|null
     */
    public function findExisting($entity, $field, $foreignId)
    {
        return $this->createQueryBuilder('i')
            ->where('i.model = :entity')
            ->andWhere('i.field = :field')
            ->andWhere('i.foreignId = :foreignId')
            ->setParameter('entity', $entity)
            ->setParameter('field', $field)
            ->setParameter('foreignId', $foreignId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
