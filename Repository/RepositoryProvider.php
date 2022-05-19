<?php declare(strict_types=1);

namespace TM\RbacBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
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
     * @var string
     */
    private $managerName;

    /**
     * @param ManagerRegistry $registry
     * @param string $permissionModelClassName
     * @param string $roleModelClassName
     * @param string $userModelClassName
     * @param string $managerName
     */
    public function __construct(
        ManagerRegistry $registry,
        string $permissionModelClassName,
        string $roleModelClassName,
        string $userModelClassName,
        string $managerName
    ) {
        $this->registry = $registry;
        $this->permissionModelClassName = $permissionModelClassName;
        $this->roleModelClassName = $roleModelClassName;
        $this->userModelClassName = $userModelClassName;
        $this->managerName = $managerName;
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
        if (null === $manager = $this->registry->getManager($this->managerName)) {
            throw new ManagerNotFoundForClassName($className);
        }

        return $manager->getRepository($className);
    }
}
