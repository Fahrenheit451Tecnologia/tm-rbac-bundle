<?php declare(strict_types=1);

namespace TM\RbacBundle\Model;

use Doctrine\Common\Collections\Collection;

interface RoleInterface
{
    /**
     * Set name
     *
     * @param string $name
     * @return void
     */
    public function setName(string $name) /* : void */;

    /**
     * Get name
     *
     * @return string
     */
    public function getName() /* : string */;

    /**
     * Set readOnly
     *
     * @param bool $readOnly
     * @return void
     */
    public function setReadOnly(bool $readOnly) /* : void */;

    /**
     * Is role "read_only"?
     *
     * @return bool
     */
    public function isReadOnly() : bool;

    /**
     * Does role have permission?
     *
     * @param PermissionInterface $permission
     * @return bool
     */
    public function hasPermission(PermissionInterface $permission) : bool;

    /**
     * Add permission to role
     *
     * @param PermissionInterface $permission
     * @return RoleInterface
     */
    public function addPermission(PermissionInterface $permission) : RoleInterface;

    /**
     * Remove permission from role
     *
     * @param PermissionInterface $permission
     * @return RoleInterface
     */
    public function removePermission(PermissionInterface $permission) : RoleInterface;

    /**
     * Get all permission
     * 
     * @return Collection|PermissionInterface[]
     */
    public function getPermissions() : Collection;
}