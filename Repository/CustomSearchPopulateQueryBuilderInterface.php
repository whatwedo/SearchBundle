<?php


namespace whatwedo\SearchBundle\Repository;


use Doctrine\ORM\QueryBuilder;

interface CustomSearchPopulateQueryBuilderInterface
{
    public function getCustomSearchPopulateQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder;

    public function customSearchPopulateCount(): int;
}
