<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Populator;

interface PopulatorInterface
{
    public function populate(?PopulateOutputInterface $output, ?string $entityClass): void;

    public function disableEntityListener(bool $disable);

    public function resetVisited() :void   ;
}
