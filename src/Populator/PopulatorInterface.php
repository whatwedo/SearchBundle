<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Populator;

interface PopulatorInterface
{
    public function populate(?PopulateOutputInterface $output, ?string $entityClasses): void;
}
