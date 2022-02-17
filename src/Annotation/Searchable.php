<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS"})
 */
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
