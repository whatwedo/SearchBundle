<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Filter;

interface FilterInterface
{
    public function process(array $data): array;

    public function getPriority(): int;
}
