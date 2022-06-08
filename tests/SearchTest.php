<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests;

use whatwedo\SearchBundle\Manager\SearchManager;
use whatwedo\SearchBundle\Populator\OneFieldPopulator;
use whatwedo\SearchBundle\Populator\PopulatorInterface;
use whatwedo\SearchBundle\Tests\App\Entity\Company;

class SearchTest extends AbstractSeaarchTest
{
    public function testSearchAll()
    {
        $this->createEntities();

        $searchManager = self::getContainer()->get(SearchManager::class);

        $result = $searchManager->searchByEntites('Mauri');

        self::assertSame(6, count($result));
    }

    public function testSearchEntity()
    {
        $this->createEntities();

        $searchManager = self::getContainer()->get(SearchManager::class);

        $result = $searchManager->searchByEntites('Mauri', [Company::class]);

        self::assertSame(1, count($result));
    }

    public function testSearchGroup()
    {
        $this->createEntities();

        $searchManager = self::getContainer()->get(SearchManager::class);

        $result = $searchManager->searchByEntites('Mauri', [], ['company']);

        self::assertSame(1, count($result));
    }

    protected function setUp(): void
    {
        /** @var OneFieldPopulator $populator */
        $populator = self::getContainer()->get(OneFieldPopulator::class);
        self::getContainer()->set(PopulatorInterface::class, $populator);
    }
}
