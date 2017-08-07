<?php declare(strict_types=1);

namespace TM\RbacBundle\Model;

use Doctrine\Common\Collections\Collection;

interface UserInterface
{
    /**
     * @return string|null
     */
    public function getUsername();

    /**
     * {@inhertidoc}
     */
    public function getRoles();

    /**
     * Get collection of user permissions (initialize collection if null)
     * 
     * @return Collection|PermissionInterface[]
     */
    public function getUserPermissions() : Collection;

    /**
     * Is permission in collection of user permissions?
     * 
     * @param PermissionInterface $permission
     * @return bool
     */
    public function hasUserPermission(PermissionInterface $permission) : bool;

    /**
     * Add permission to collection of user permission
     * 
     * @param PermissionInterface $permission
     * @return UserInterface
     */
    public function addUserPermission(PermissionInterface $permission) : UserInterface;

    /**
     * Remove permission from collection of user permissions
     * 
     * @param PermissionInterface $permission
     * @return UserInterface
     */
    public function removeUserPermission(PermissionInterface $permission) : UserInterface;

    /**
     * Get collection of user roles (initialize collection if null)
     * 
     * @return Collection|RoleInterface[]
     */
    public function getUserRoles() : Collection;

    /**
     * Is role in collection of user roles?
     * 
     * @param RoleInterface $role
     * @return bool
     */
    public function hasUserRole(RoleInterface $role) : bool;

    /**
     * Add role to collection of user role
     * 
     * @param RoleInterface $role
     * @return UserInterface
     */
    public function addUserRole(RoleInterface $role) : UserInterface;

    /**
     * Remove role from collection of user roles
     * 
     * @param RoleInterface $role
     * @return UserInterface
     */
    public function removeUserRole(RoleInterface $role) : UserInterface;

    /**
     * Does user have the "super_admin" role?
     *
     * @return bool
     */
    public function isSuperAdmin();
}