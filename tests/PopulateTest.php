<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use whatwedo\SearchBundle\Entity\Index;
use whatwedo\SearchBundle\Populator\PopulatorInterface;
use whatwedo\SearchBundle\Populator\StandardPopulator;

class PopulateTest extends AbstractSearchTest
{
    public function testPopulate()
    {
        $this->_resetSchema();
        $this->_resetDatabase();

        $this->createEntities();

        /** @var PopulatorInterface $populator */
        $populator = self::getContainer()->get(StandardPopulator::class);

        $populator->populate();

        $this->assertSame(1100, self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Index::class)->count([]));
    }
}
