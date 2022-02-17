<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use whatwedo\SearchBundle\Entity\Index;
use Zenstruck\Console\Test\InteractsWithConsole;

class PopulateCommandTest extends AbstractIndexTest
{
    use InteractsWithConsole;

    public function testPopulateCommand()
    {
        $this->_resetSchema();
        $this->_resetDatabase();

        $this->createEntities();

        $this->executeConsoleCommand('whatwedo:search:populate')
            ->assertSuccessful()
            ->assertOutputContains('Flushing index table')
            ->assertOutputContains('Entity\Company')
            ->assertOutputContains('Entity\Contact')
        ;

        $this->assertSame(140, self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Index::class)->count([]));
    }
}
