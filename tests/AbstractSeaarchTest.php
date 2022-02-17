<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use whatwedo\SearchBundle\Tests\App\Factory\CompanyFactory;
use whatwedo\SearchBundle\Tests\App\Factory\ContactFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class AbstractSeaarchTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    protected function createEntities()
    {
        CompanyFactory::createOne([
            'name' => 'whatwedo GmbH',
            'city' => 'Bern',
            'country' => 'Switzerland',
            'taxIdentificationNumber' => '001.002.003',
        ]);
        CompanyFactory::createOne([
            'name' => 'Swisscom',
            'city' => 'Bern',
            'country' => 'Switzerland',
            'taxIdentificationNumber' => '001.002.004',
        ]);
        CompanyFactory::createOne([
            'name' => 'SBB',
            'city' => 'Bern',
            'country' => 'Switzerland',
            'taxIdentificationNumber' => '001.002.005',
        ]);
        CompanyFactory::createOne([
            'name' => 'Sunrise',
            'city' => 'Zürich',
            'country' => 'Switzerland',
            'taxIdentificationNumber' => '001.002.008',
        ]);
        CompanyFactory::createOne([
            'name' => 'The Company',
            'city' => 'Los Angeles',
            'country' => 'USA',
            'taxIdentificationNumber' => '001.001.003',
        ]);
        CompanyFactory::createOne([
            'name' => 'Mauri Company',
            'city' => 'Bümplitz',
            'country' => 'Switzerland',
            'taxIdentificationNumber' => '001.001.003',
        ]);

        ContactFactory::createOne([
            'name' => 'Maurizio Monticelli',
            'company' => CompanyFactory::findOrCreate([
                'name' => 'whatwedo GmbH',
            ]),
        ]);
        ContactFactory::createOne([
            'name' => 'Maurizio Monticelli',
            'company' => CompanyFactory::findOrCreate([
                'name' => 'Swisscom',
            ]),
        ]);
        ContactFactory::createOne([
            'name' => 'Maurizio Monticelli',
            'company' => CompanyFactory::findOrCreate([
                'name' => 'SBB',
            ]),
        ]);
        ContactFactory::createOne([
            'name' => 'Maurizio Monticelli',
            'company' => CompanyFactory::findOrCreate([
                'name' => 'Sunrise',
            ]),
        ]);
        ContactFactory::createOne([
            'name' => 'Maurizio Monticelli',
            'company' => CompanyFactory::findOrCreate([
                'name' => 'The Company',
            ]),
        ]);
    }
}
