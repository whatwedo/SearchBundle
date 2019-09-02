<?php


namespace whatwedo\SearchBundle\Entity;


interface PostSearchInterface
{
    public function postSearch(array $ids, string $query, ? string $entity, ? string $field) : array;
}
