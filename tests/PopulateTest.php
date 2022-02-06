<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use whatwedo\SearchBundle\Entity\Index;
use whatwedo\SearchBundle\Exception\ClassNotDoctrineMappedException;
use whatwedo\SearchBundle\Exception\ClassNotIndexedEntityException;
use whatwedo\SearchBundle\Populator\PopulatorInterface;
use whatwedo\SearchBundle\Tests\App\Entity\Company;
use whatwedo\SearchBundle\Tests\App\Entity\Person;
use whatwedo\SearchBundle\Tests\App\Model\NotADoctrinieModel;

class PopulateTest extends AbstractSearchTest
{
    public function testPopulate()
    {
        $this->createEntities();

        /** @var PopulatorInterface $populator */
        $populator = self::getContainer()->get(PopulatorInterface::class);

        $populator->populate();

        $this->assertSame(140, self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Index::class)->count([]));
    }

    public function testPopulateCompanies()
    {
        $this->createEntities();

        /** @var PopulatorInterface $populator */
        $populator = self::getContainer()->get(PopulatorInterface::class);

        $populator->populate(null, Company::class);

        $this->assertSame(40, self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Index::class)->count([]));
    }

    public function testPopulateNotEntity()
    {
        /** @var PopulatorInterface $populator */
        $populator = self::getContainer()->get(PopulatorInterface::class);

        $this->expectException(ClassNotDoctrineMappedException::class);

        $populator->populate(null, NotADoctrinieModel::class);
    }

    public function testPopulateNotIndexEntity()
    {
        /** @var PopulatorInterface $populator */
        $populator = self::getContainer()->get(PopulatorInterface::class);

        $this->expectException(ClassNotIndexedEntityException::class);

        $populator->populate(null, Person::class);
    }
}
