<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests\Fixtures\Factory;

use whatwedo\SearchBundle\Tests\Fixtures\Entity\Contact;
use whatwedo\SearchBundle\Tests\Fixtures\Repository\ContactRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @method static        Contact|Proxy createOne(array $attributes = [])
 * @method static        Contact[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static        Contact|Proxy find($criteria)
 * @method static        Contact|Proxy findOrCreate(array $attributes)
 * @method static        Contact|Proxy first(string $sortedField = 'id')
 * @method static        Contact|Proxy last(string $sortedField = 'id')
 * @method static        Contact|Proxy random(array $attributes = [])
 * @method static        Contact|Proxy randomOrCreate(array $attributes = [])
 * @method static        Contact[]|Proxy[] all()
 * @method static        Contact[]|Proxy[] findBy(array $attributes)
 * @method static        Contact[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static        Contact[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static        ContactRepository|RepositoryProxy repository()
 * @method Contact|Proxy create($attributes = [])
 */
final class ContactFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->name(),
            'company' => CompanyFactory::randomOrCreate(),
        ];
    }

    protected static function getClass(): string
    {
        return Contact::class;
    }
}
