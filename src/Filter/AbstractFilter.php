<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Filter;

abstract class AbstractFilter implements FilterInterface
{
    public function getPriority(): int
    {
        return 0;
    }
}
