<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Model;

class ResultItem
{
    public function __construct(
        private int $id,
        private string $class,
        private string $score,
        private $entity
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getScore(): string
    {
        return $this->score;
    }

    public function getEntity()
    {
        return $this->entity;
    }
}
