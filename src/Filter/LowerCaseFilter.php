<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Filter;

class LowerCaseFilter extends AbstractFilter
{
    public function process(array $data): array
    {
        return array_map(
            fn (string $item) => strtolower($item),
            $data
        );
    }
}
