<?php


namespace whatwedo\SearchBundle\Entity;


interface PostSearchInterface
{
    public function postSearch(array $ids) : array;
}
