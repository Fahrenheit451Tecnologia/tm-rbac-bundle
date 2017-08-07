<?php declare(strict_types=1);

namespace TM\RbacBundle\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;
use TM\RbacBundle\Exception\ManagerNotFoundForClassName;

class RepositoryProvider
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var string
     */
    private $permissionModelClassName;

    /**
     * @var string
     */
    private $roleModelClassName;

    /**
     * @var string
     */
    private $userModelClassName;

    /**
     * @param ManagerRegistry $registry
     * @param string $permissionModelClassName
     * @param string $roleModelClassName
     * @param string $userModelClassName
     */
    public function __construct(
        ManagerRegistry $registry,
        string $permissionModelClassName,
        string $roleModelClassName,
        string $userModelClassName
    ) {
        $this->registry = $registry;
        $this->permissionModelClassName = $permissionModelClassName;
        $this->roleModelClassName = $roleModelClassName;
        $this->userModelClassName = $userModelClassName;
    }

    /**
     * @return ObjectRepository|PermissionRepositoryInterface
     */
    public function getPermissionRepository() : PermissionRepositoryInterface
    {
        return $this->getRepository($this->permissionModelClassName);
    }

    /**
     * @return ObjectRepository|RoleRepositoryInterface
     */
    public function getRoleRepository() : RoleRepositoryInterface
    {
        return $this->getRepository($this->roleModelClassName);
    }

    /**
     * @return ObjectRepository|UserRepositoryInterface
     */
    public function getUserRepository() : UserRepositoryInterface
    {
        return $this->getRepository($this->userModelClassName);
    }

    /**
     * @param string $className
     * @return ObjectRepository
     */
    private function getRepository(string $className) : ObjectRepository
    {
        if (null === $manager = $this->registry->getManagerForClass($className)) {
            throw new ManagerNotFoundForClassName($className);
        }
        
        return $manager->getRepository($className);
    }
}