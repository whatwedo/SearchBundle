<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use whatwedo\SearchBundle\Manager\FilterManager;
use whatwedo\SearchBundle\Manager\IndexManager;
use whatwedo\SearchBundle\Manager\SearchManager;

class WiringTest extends KernelTestCase
{
    public function testServiceWiring()
    {
        foreach ([
            IndexManager::class,
            SearchManager::class,
            FilterManager::class,
        ] as $serviceClass) {
            $this->assertInstanceOf(
                $serviceClass,
                self::getContainer()->get($serviceClass)
            );
        }
    }
}
