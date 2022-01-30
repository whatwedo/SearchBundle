<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests\Fixtures\Factory;

use whatwedo\SearchBundle\Tests\Fixtures\Entity\Person;
use whatwedo\SearchBundle\Tests\Fixtures\Repository\PersonRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @method static       Person|Proxy createOne(array $attributes = [])
 * @method static       Person[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static       Person|Proxy find($criteria)
 * @method static       Person|Proxy findOrCreate(array $attributes)
 * @method static       Person|Proxy first(string $sortedField = 'id')
 * @method static       Person|Proxy last(string $sortedField = 'id')
 * @method static       Person|Proxy random(array $attributes = [])
 * @method static       Person|Proxy randomOrCreate(array $attributes = [])
 * @method static       Person[]|Proxy[] all()
 * @method static       Person[]|Proxy[] findBy(array $attributes)
 * @method static       Person[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static       Person[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static       PersonRepository|RepositoryProxy repository()
 * @method Person|Proxy create($attributes = [])
 */
final class PersonFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->name(),
        ];
    }

    protected static function getClass(): string
    {
        return Person::class;
    }
}
