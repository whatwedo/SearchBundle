<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tokenizer;

class StandardTokenizer extends AbstractTokenizer
{
    public function __construct(
        protected string $separator = ' '
    ) {
    }

    public function tokenize(string $data): array
    {
        return explode($this->separator, $data);
    }
}
