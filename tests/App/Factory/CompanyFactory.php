<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests\App\Factory;

use whatwedo\SearchBundle\Tests\App\Entity\Company;
use whatwedo\SearchBundle\Tests\App\Repository\CompanyRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @method static        Company|Proxy createOne(array $attributes = [])
 * @method static        Company[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static        Company|Proxy find($criteria)
 * @method static        Company|Proxy findOrCreate(array $attributes)
 * @method static        Company|Proxy first(string $sortedField = 'id')
 * @method static        Company|Proxy last(string $sortedField = 'id')
 * @method static        Company|Proxy random(array $attributes = [])
 * @method static        Company|Proxy randomOrCreate(array $attributes = [])
 * @method static        Company[]|Proxy[] all()
 * @method static        Company[]|Proxy[] findBy(array $attributes)
 * @method static        Company[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static        Company[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static        CompanyRepository|RepositoryProxy repository()
 * @method Company|Proxy create($attributes = [])
 */
final class CompanyFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->company(),
            'city' => self::faker()->city(),
            'country' => self::faker()->country(),
            'taxIdentificationNumber' => self::faker()->numerify(self::faker()->countryCode() . '###.####.###.#.###.##'),
        ];
    }

    protected static function getClass(): string
    {
        return Company::class;
    }
}
