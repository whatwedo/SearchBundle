# Indexing

By default all entities will be indexed by the populate command


```
php bin/console whatwedo:search:populate
```

## QueryBuilder

You can define your own subset of entities by define a queryBuilder
for the indexing. Do this by implementing the interface `whatwedo\SearchBundle\Repository\CustomSearchPopulateQueryBuilderInterface`

for example if you want to index only the product in stock.

```php


use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use whatwedo\SearchBundle\Repository\CustomSearchPopulateQueryBuilderInterface;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository implements CustomSearchPopulateQueryBuilderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function getCustomSearchPopulateQueryBuilder(string $alias = 'e', ?string $indexBy = null): QueryBuilder
    {
        return $this->createQueryBuilder($alias, $indexBy)
            ->where($alias.'.inStock = true');
    }

    public function customSearchPopulateCount(): int
    {
        $qb = $this->getCustomSearchPopulateQueryBuilder('e');
        return $qb->select($qb->expr()->count('e'))
            ->getQuery()
            ->getSingleScalarResult();
    }
}


```



