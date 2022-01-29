<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests\Fixtures\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use whatwedo\SearchBundle\Tests\Fixtures\Entity\Contact;

/**
 * @method Contact|null      find($id, $lockMode = null, $lockVersion = null)
 * @method Contact|null      findOneBy(array $criteria, array $orderBy = null)
 * @method array<Department> findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Contact           findOneByName(string $name)
 */
final class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }
}