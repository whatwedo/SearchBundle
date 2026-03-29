<?php

namespace whatwedo\SearchBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Searchable
{
    /**
     * @var string
     */
    public $preSearch;

    /**
     * @var string
     */
    public $postSearch;

    /**
     * @return string
     */
    public function getPreSearch(): ? string
    {
        return $this->preSearch;
    }

    /**
     * @return string
     */
    public function getPostSearch(): ? string
    {
        return $this->postSearch;
    }
}
