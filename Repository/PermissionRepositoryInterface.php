<?php declare(strict_types=1);

namespace TM\RbacBundle\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use TM\RbacBundle\Model\PermissionInterface;
use TM\RbacBundle\Model\RoleInterface;

interface PermissionRepositoryInterface extends ObjectRepository
{
    /**
     * @return array|string[]
     */
    public function getAllPermissionsKeys() : array;

    /**
     * @param string $name
     * @return PermissionInterface|null
     */
    public function findOneByName(string $name) /* : ?PermissionInterface */;

    /**
     * @param string $name
     * @return PermissionInterface
     * @throws \Exception           When permissions is not found
     */
    public function getOneByName(string $name) : PermissionInterface;

    /**
     * @param string $id
     * @param string $name
     * @return PermissionInterface
     */
    public function createNew(string $id, string $name) : PermissionInterface;

    /**
     * @param PermissionInterface $permission
     * @return void
     */
    public function save(PermissionInterface $permission) /* : void */;

    /**
     * @param PermissionInterface $permission
     * @return void
     */
    public function delete(PermissionInterface $permission) /* : void */;

    /**
     * @param RoleInterface $role
     * @return array
     */
    public function findAllPermissionsNotUsedByRole(RoleInterface $role) : array;
}