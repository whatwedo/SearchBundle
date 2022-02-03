<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests\App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use whatwedo\SearchBundle\Tests\App\Entity\Person;

/**
 * @method Person|null   find($id, $lockMode = null, $lockVersion = null)
 * @method Person|null   findOneBy(array $criteria, array $orderBy = null)
 * @method array<Person> findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Person        findOneByName(string $name)
 */
final class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }
}
