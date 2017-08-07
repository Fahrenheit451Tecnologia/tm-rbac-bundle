<?php declare(strict_types=1);

namespace TM\RbacBundle\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use TM\RbacBundle\Model\RoleInterface;

interface RoleRepositoryInterface extends ObjectRepository
{
    /**
     * @param string $name
     * @return RoleInterface|null
     */
    public function findOneByName(string $name) /* : ?RoleInterface */;

    /**
     * @param string $name
     * @return RoleInterface
     * @throws \Exception           When roles is not found
     */
    public function getOneByName(string $name) : RoleInterface;

    /**
     * @return RoleInterface
     */
    public function createNew() : RoleInterface;

    /**
     * @param RoleInterface $role
     * @return void
     */
    public function delete(RoleInterface $role) /* : void */;

    /**
     * @param RoleInterface $role
     * @return void
     */
    public function save(RoleInterface $role) /* : void */;
}