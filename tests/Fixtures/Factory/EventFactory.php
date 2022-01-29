<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests\Fixtures\Factory;

use whatwedo\SearchBundle\Tests\Fixtures\Entity\Event;
use whatwedo\SearchBundle\Tests\Fixtures\Repository\EventRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @method static      Event|Proxy createOne(array $attributes = [])
 * @method static      Event[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static      Event|Proxy find($criteria)
 * @method static      Event|Proxy findOrCreate(array $attributes)
 * @method static      Event|Proxy first(string $sortedField = 'id')
 * @method static      Event|Proxy last(string $sortedField = 'id')
 * @method static      Event|Proxy random(array $attributes = [])
 * @method static      Event|Proxy randomOrCreate(array $attributes = [])
 * @method static      Event[]|Proxy[] all()
 * @method static      Event[]|Proxy[] findBy(array $attributes)
 * @method static      Event[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static      Event[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static      EventRepository|RepositoryProxy repository()
 * @method Event|Proxy create($attributes = [])
 */
final class EventFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
        ];
    }

    protected function initialize(): self
    {
        // see https://github.com/zenstruck/foundry#initialization
        return $this
            // ->afterInstantiate(function(Address $address) {})
            ;
    }

    protected static function getClass(): string
    {
        return Event::class;
    }
}
