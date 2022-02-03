<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Filter;

class RemoveFilter extends AbstractFilter
{
    public function __construct(
        protected array $remove
    ) {
    }

    public function process(array $data): array
    {
        return array_values(array_filter($data, fn ($item) => ! in_array($item, $this->remove, true)));
    }
}
