<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use whatwedo\SearchBundle\Entity\Index;
use whatwedo\SearchBundle\Populator\OneFieldPopulator;

class OneFieldPopulateTest extends AbstractSearchTest
{
    public function testPopulate()
    {
        /** @var OneFieldPopulator $populator */
        $populator = self::getContainer()->get(OneFieldPopulator::class);

        $this->createEntities();

        $populator->populate();

        $this->assertSame(110, self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Index::class)->count([]));
    }
}
