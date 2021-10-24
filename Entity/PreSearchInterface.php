<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Entity;

use Doctrine\ORM\QueryBuilder;

interface PreSearchInterface
{
    public function preSearch(QueryBuilder &$qb, string $query, ? string $entity, ? string $field): void;
}
