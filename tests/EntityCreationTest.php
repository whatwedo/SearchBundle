<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use whatwedo\SearchBundle\Tests\Fixtures\Entity\Company;
use whatwedo\SearchBundle\Tests\Fixtures\Entity\Contact;
use whatwedo\SearchBundle\Tests\Fixtures\Factory\CompanyFactory;
use whatwedo\SearchBundle\Tests\Fixtures\Factory\ContactFactory;
use whatwedo\SearchBundle\Tests\Helper\ResetDatabase;
use Zenstruck\Foundry\Test\Factories;

class EntityCreationTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    public function testCreateCompanies()
    {
        $this->_resetDatabase();
        $entities = CompanyFactory::new()->withoutPersisting()->createMany(100);

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        foreach ($entities as $entity) {
            $em->persist($entity->object());
        }
        $em->flush();
        $this->assertSame(100, $em->getRepository(Company::class)->count([]));
    }

    public function testCreateContacts()
    {
        $this->_resetDatabase();
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $this->_resetDatabase();
        $entities = CompanyFactory::new()->withoutPersisting()->createMany(100);
        foreach ($entities as $entity) {
            $em->persist($entity->object());
        }

        $em->flush();

        $entities = ContactFactory::new()->withoutPersisting()->createMany(1000);

        foreach ($entities as $entity) {
            $em->persist($entity->object());
        }

        $em->flush();

        $this->assertSame(100, $em->getRepository(Company::class)->count([]));
        $this->assertSame(1000, $em->getRepository(Contact::class)->count([]));
    }
}
