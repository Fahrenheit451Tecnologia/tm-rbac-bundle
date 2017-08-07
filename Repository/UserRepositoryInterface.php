<?php declare(strict_types=1);

namespace TM\RbacBundle\Repository;

use Pagerfanta\Pagerfanta;
use TM\RbacBundle\Model\PermissionInterface;
use TM\RbacBundle\Model\RoleInterface;
use TM\RbacBundle\Model\UserInterface;

interface UserRepositoryInterface
{
    /**
     * Create paginator of users with a given permission
     *
     * @param PermissionInterface $permission
     * @param array $sorting
     * @param int $page
     * @param int $limit
     * @return Pagerfanta
     */
    public function createUserWithPermissionPaginator(
        PermissionInterface $permission,
        array $sorting = [],
        $page = 1,
        $limit = 50
    ) : Pagerfanta;

    /**
     * Create paginator of users with a given role
     *
     * @param RoleInterface $role
     * @param array $sorting
     * @param int $page
     * @param int $limit
     * @return Pagerfanta
     */
    public function createUserWithRolePaginator(
        RoleInterface $role,
        array $sorting = [],
        $page = 1,
        $limit = 50
    ) : Pagerfanta;

    /**
     * @param UserInterface $user
     * @return void
     */
    public function save(UserInterface $user) /* : void */;
}