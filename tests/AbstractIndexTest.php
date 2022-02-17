<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use whatwedo\SearchBundle\Tests\App\Entity\Company;
use whatwedo\SearchBundle\Tests\App\Entity\Contact;
use whatwedo\SearchBundle\Tests\App\Factory\CompanyFactory;
use whatwedo\SearchBundle\Tests\App\Factory\ContactFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class AbstractIndexTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    protected function createEntities()
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        CompanyFactory::createMany(10);

        ContactFactory::createMany(100);

        $this->assertSame(10, $em->getRepository(Company::class)->count([]));
        $this->assertSame(100, $em->getRepository(Contact::class)->count([]));
    }
}
