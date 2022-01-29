<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tokenizer;

interface TokenizerInterface
{
    public function tokenize(string $data): array;

    public function getPriority(): int;
}
