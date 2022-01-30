<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use whatwedo\SearchBundle\Entity\Index;
use whatwedo\SearchBundle\Tests\Fixtures\Factory\ContactFactory;

class IndexListenerTest extends AbstractSearchTest
{
    public function testEntityCreation()
    {
        $this->_resetSchema();
        $this->_resetDatabase();

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $this->assertSame(0, $em->getRepository(Index::class)->count([]));

        ContactFactory::createOne();

        $this->assertSame(5, $em->getRepository(Index::class)->count([]));
    }
}
