<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Populator;

class NullPopulateOutput implements PopulateOutputInterface
{
    public function log(string $string)
    {
    }

    public function progressStart(int $max): void
    {
    }

    public function progressFinish(): void
    {
    }

    public function setProgress(int $i): void
    {
    }
}
