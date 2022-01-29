<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests\Fixtures\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use whatwedo\SearchBundle\Tests\Fixtures\Entity\Department;

/**
 * @method Department|null   find($id, $lockMode = null, $lockVersion = null)
 * @method Department|null   findOneBy(array $criteria, array $orderBy = null)
 * @method array<Department> findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method array<Department> findByName(string $name)
 * @method Department        findOneByName(string $name)
 */
final class DepartmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Department::class);
    }
}
