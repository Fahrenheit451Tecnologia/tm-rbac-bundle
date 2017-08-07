<?php declare(strict_types=1);

namespace TM\RbacBundle\Repository\Traits;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

trait PaginationTrait
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param int|null $page
     * @param int|null $limit
     * @return Pagerfanta
     */
    protected function getPaginator(QueryBuilder $queryBuilder, int $page = null, int $limit = null) : Pagerfanta
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($queryBuilder));
        $paginator->setAllowOutOfRangePages(true);

        if (null !== $limit) {
            $paginator->setMaxPerPage($limit);
        }

        if (null !== $page) {
            $paginator->setCurrentPage($page);
        }

        return $paginator;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $criteria
     * @param string $alias
     */
    protected function applyCriteria(QueryBuilder $queryBuilder, array $criteria = [], string $alias = 'o') /* : void */
    {
        foreach ($criteria as $property => $value) {
            $name = $this->validatePropertyName($property, $alias);
            if (null === $value) {
                $queryBuilder->andWhere($queryBuilder->expr()->isNull($name));
            } elseif (is_array($value)) {
                $queryBuilder->andWhere($queryBuilder->expr()->in($name, $value));
            } elseif ('' !== $value) {
                $parameter = str_replace('.', '_', $property);
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->eq($name, ':'.$parameter))
                    ->setParameter($parameter, $value)
                ;
            }
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $sorting
     * @param string $alias
     */
    protected function applySorting(QueryBuilder $queryBuilder, array $sorting = [], string $alias = 'o') /* : void */
    {
        foreach ($sorting as $property => $order) {
            if (!empty($order)) {
                $queryBuilder->addOrderBy($this->validatePropertyName($property, $alias), $order);
            }
        }
    }

    /**
     * @param string $name
     * @param string $alias
     * @return string
     */
    protected function validatePropertyName(string $name, string $alias = 'o') : string
    {
        if (false === strpos($name, '.')) {
            $name = sprintf('%s.%s', $alias, $name);
        }

        return $name;
    }
}