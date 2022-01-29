<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests\Fixtures\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use whatwedo\SearchBundle\Tests\Fixtures\Entity\Event;

/**
 * @method Event|null   find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null   findOneBy(array $criteria, array $orderBy = null)
 * @method array<Event> findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }
}
