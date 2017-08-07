<?php declare(strict_types=1);

namespace TM\RbacBundle\Model\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use TM\RbacBundle\Model\PermissionInterface;
use TM\RbacBundle\Model\RoleInterface;
use TM\RbacBundle\Model\UserInterface;

trait UserTrait
{
    /**
     * @ORM\ManyToMany(targetEntity="\TM\RbacBundle\Model\PermissionInterface")
     * @ORM\JoinTable(name="tm_user_permission",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="permission_id", referencedColumnName="id")}
     * )
     * @JsonApi\HasMany(includeByDefault=false)
     *
     * @var ArrayCollection|PermissionInterface[]
     */
    protected $userPermissions;

    /**
     * @ORM\ManyToMany(targetEntity="\TM\RbacBundle\Model\RoleInterface")
     * @ORM\JoinTable(name="tm_user_role",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     * )
     * @JsonApi\HasMany(includeByDefault=false)
     * @Serializer\Expose
     *
     * @var ArrayCollection|RoleInterface[]
     */
    protected $userRoles;

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        $permissions = [];

        /** @var PermissionInterface $permission */
        foreach ($this->getUserPermissions() as $permission) {
            $permissions[] = $permission->getId();
        }

        /** @var RoleInterface $role */
        foreach ($this->getUserRoles() as $role) {
            /** @var PermissionInterface $permission */
            foreach ($role->getPermissions() as $permission) {
                $permissions[] = $permission->getId();
            }
        }

        return array_unique($permissions);
    }

    /**
     * Get collection of user permissions (initialize collection if null)
     *
     * @return Collection|PermissionInterface[]
     */
    public function getUserPermissions() : Collection
    {
        if (null === $this->userPermissions) {
            $this->userPermissions = new ArrayCollection();
        }

        return $this->userPermissions;
    }

    /**
     * Is permission in collection of user permissions?
     * 
     * @param PermissionInterface $permission
     * @return bool
     */
    public function hasUserPermission(PermissionInterface $permission) : bool
    {
        return (bool) $this->getUserPermissions()->filter(
            function(PermissionInterface $p) use ($permission) {
                return $p->getId() === $permission->getId();
            }
        )->first();
    }

    /**
     * Add permission to collection of user permission
     * 
     * @param PermissionInterface $permission
     * @return UserInterface
     */
    public function addUserPermission(PermissionInterface $permission) : UserInterface
    {
        if (!$this->hasUserPermission($permission)) {
            $this->getUserPermissions()->add($permission);
        }

        return $this;
    }

    /**
     * Remove permission from collection of user permissions
     * 
     * @param PermissionInterface $permission
     * @return UserInterface
     */
    public function removeUserPermission(PermissionInterface $permission) : UserInterface
    {
        if ($this->hasUserPermission($permission)) {
            $this->getUserPermissions()->removeElement($permission);
        }

        return $this;
    }

    /**
     * Get collection of user roles (initialize collection if null)
     * 
     * @return Collection|RoleInterface[]
     */
    public function getUserRoles() : Collection
    {
        if (null === $this->userRoles) {
            $this->userRoles = new ArrayCollection();
        }

        return $this->userRoles;
    }

    /**
     * Is role in collection of user roles?
     * 
     * @param RoleInterface $role
     * @return bool
     */
    public function hasUserRole(RoleInterface $role) : bool
    {
        return (bool) $this->getUserRoles()->filter(
            function(RoleInterface $r) use ($role) {
                return $r->getName() === $role->getName();
            }
        )->first();
    }

    /**
     * Add role to collection of user role
     * 
     * @param RoleInterface $role
     * @return UserInterface
     */
    public function addUserRole(RoleInterface $role) : UserInterface
    {
        if (!$this->hasUserRole($role)) {
            $this->getUserRoles()->add($role);
        }

        return $this;
    }

    /**
     * Remove role from collection of user roles
     * 
     * @param RoleInterface $role
     * @return UserInterface
     */
    public function removeUserRole(RoleInterface $role) : UserInterface
    {
        if ($this->hasUserRole($role)) {
            $this->getUserRoles()->removeElement($role);
        }

        return $this;
    }
}