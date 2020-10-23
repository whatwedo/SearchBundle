<?php

namespace whatwedo\SearchBundle\Entity;

interface PostSearchInterface
{
    public function postSearch(array $queryResults, string $query, ? string $entity, ? string $field): array;
}
