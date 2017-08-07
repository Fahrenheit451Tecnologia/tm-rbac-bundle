<?php declare(strict_types=1);

namespace TM\RbacBundle\Repository\Traits;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Pagerfanta;
use TM\RbacBundle\Model\PermissionInterface;
use TM\RbacBundle\Model\RoleInterface;

trait UserRepositoryTrait
{
    use PaginationTrait;

    /**
     * {@inheritdoc}
     */
    public function createUserWithPermissionPaginator(
        PermissionInterface $permission,
        array $sorting = [],
        $page = 1,
        $limit = 50
    ) : Pagerfanta {
        $queryBuilder = $this
            ->createQueryBuilder('u')
        ;

        $queryBuilder
            ->leftJoin('u.userPermissions', 'up')
            ->leftJoin('u.userRoles', 'ur')
            ->leftJoin('ur.permissions', 'urp')

            ->where($queryBuilder->expr()->orX(
                $queryBuilder->expr()->eq('up', ':permission'),
                $queryBuilder->expr()->eq('urp', ':permission')
            ))

            ->setParameter('permission', $permission)
        ;

        if (empty($sorting)) {
            if (!is_array($sorting)) {
                $sorting = [$sorting];
            }

            $sorting['username'] = 'ASC';
        }

        $this->applySorting($queryBuilder, $sorting, 'u');

        return $this->getPaginator($queryBuilder, $page, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function createUserWithRolePaginator(
        RoleInterface $role,
        array $sorting = [],
        $page = 1,
        $limit = 50
    ) : Pagerfanta {
        $queryBuilder = $this
            ->createQueryBuilder('u')
        ;

        $queryBuilder
            ->leftJoin('u.userRoles', 'ur')
            ->where($queryBuilder->expr()->eq('ur', ':role'))
            ->setParameter('role', $role)
        ;

        if (empty($sorting)) {
            if (!is_array($sorting)) {
                $sorting = [$sorting];
            }

            $sorting['username'] = 'ASC';
        }

        $this->applySorting($queryBuilder, $sorting, 'u');

        return $this->getPaginator($queryBuilder, $page, $limit);
    }

    /**
     * @return QueryBuilder
     */
    abstract public function createQueryBuilder($alias, $indexBy = null);
}