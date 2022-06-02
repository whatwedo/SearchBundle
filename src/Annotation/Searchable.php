<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Annotation;

#[\Attribute]
class Searchable
{
    public function __construct(
        public ?string $preSearch,
        public ?string $postSearch
    ) {
    }

    public function getPreSearch(): ? string
    {
        return $this->preSearch;
    }

    public function getPostSearch(): ? string
    {
        return $this->postSearch;
    }
}
