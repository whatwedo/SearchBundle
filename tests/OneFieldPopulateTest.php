<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use whatwedo\SearchBundle\Entity\Index;
use whatwedo\SearchBundle\Populator\OneFieldPopulator;
use whatwedo\SearchBundle\Populator\PopulatorInterface;

class OneFieldPopulateTest extends AbstractIndexTest
{
    protected function setUp(): void
    {
        /** @var OneFieldPopulator $populator */
        $populator = self::getContainer()->get(OneFieldPopulator::class);
        self::getContainer()->set(PopulatorInterface::class, $populator);
    }

    public function testPopulate()
    {
        /** @var OneFieldPopulator $populator */
        $populator = self::getContainer()->get(PopulatorInterface::class);

        $this->createEntities();

        $populator->populate();

        $this->assertSame(330, self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Index::class)->count([]));
    }

    public function testListnerPopulate()
    {
        $this->createEntities();

        $this->assertSame(330, self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Index::class)->count([]));
    }

    public function testDisableListnerPopulate()
    {
        /** @var OneFieldPopulator $populator */
        $populator = self::getContainer()->get(PopulatorInterface::class);

        $populator->disableEntityListener(true);

        $this->createEntities();

        $this->assertSame(0, self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Index::class)->count([]));
    }
}
