<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use whatwedo\SearchBundle\Exception\ImportNotValidException;
use whatwedo\SearchBundle\Manager\ImportManager;
use whatwedo\SearchBundle\Manager\IndexManager;
use whatwedo\SearchBundle\Manager\SearchManager;
use whatwedo\SearchBundle\Model\ImportResultList;
use whatwedo\SearchBundle\Tests\Fixtures\Definition\EventImportDefinition;
use whatwedo\SearchBundle\Tests\Fixtures\Factory\DepartmentFactory;
use whatwedo\SearchBundle\Tests\Helper\ResetDatabase;
use Zenstruck\Foundry\Test\Factories;

class WiringTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    public function testServiceWiring()
    {
        $importManager = self::getContainer()->get(IndexManager::class);
        $this->assertInstanceOf(IndexManager::class, $importManager);

        $searchManager = self::getContainer()->get(SearchManager::class);
        $this->assertInstanceOf(SearchManager::class, $searchManager);
    }
}
