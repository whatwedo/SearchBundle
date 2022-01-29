<?php

declare(strict_types=1);

namespace whatwedo\ImportBundle\Tests\Fixtures\Factory;

use whatwedo\ImportBundle\Tests\Fixtures\Entity\Department;
use whatwedo\ImportBundle\Tests\Fixtures\Repository\DepartmentRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @method static           Department|Proxy createOne(array $attributes = [])
 * @method static           Department[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static           Department|Proxy find($criteria)
 * @method static           Department|Proxy findOrCreate(array $attributes)
 * @method static           Department|Proxy first(string $sortedField = 'id')
 * @method static           Department|Proxy last(string $sortedField = 'id')
 * @method static           Department|Proxy random(array $attributes = [])
 * @method static           Department|Proxy randomOrCreate(array $attributes = [])
 * @method static           Department[]|Proxy[] all()
 * @method static           Department[]|Proxy[] findBy(array $attributes)
 * @method static           Department[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static           Department[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static           DepartmentRepository|RepositoryProxy repository()
 * @method Department|Proxy create($attributes = [])
 */
final class DepartmentFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [];
    }

    protected static function getClass(): string
    {
        return Department::class;
    }
}
