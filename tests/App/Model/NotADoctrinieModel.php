<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests\App\Model;

class NotADoctrinieModel
{
    protected string $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
