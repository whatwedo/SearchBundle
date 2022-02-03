<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests\App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use whatwedo\SearchBundle\Tests\App\Entity\Contact;

/**
 * @method Contact|null   find($id, $lockMode = null, $lockVersion = null)
 * @method Contact|null   findOneBy(array $criteria, array $orderBy = null)
 * @method array<Contact> findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Contact        findOneByName(string $name)
 */
final class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }
}
