<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use whatwedo\SearchBundle\Tests\Helper\ResetDatabase;
use Zenstruck\Console\Test\InteractsWithConsole;
use Zenstruck\Foundry\Test\Factories;

class PopulateTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;
    use InteractsWithConsole;

    public function testPopulateCommand()
    {
        $this->executeConsoleCommand('whatwedo:search:populate')
            ->assertSuccessful() // command exit code is 0
        ;
    }
}
