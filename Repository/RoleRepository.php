<?php declare(strict_types=1);

namespace TM\RbacBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Pagerfanta;
use TM\RbacBundle\Model\RoleInterface;
use TM\RbacBundle\Repository\Traits\PaginationTrait;

abstract class RoleRepository extends EntityRepository
{
    use PaginationTrait;

    /**
     * {@inheritdoc}
     */
    public function createPaginator(array $sorting = [], int $page = 1, int $limit = 50) : Pagerfanta
    {
        $queryBuilder = $this
            ->createQueryBuilder('o')
        ;

        if (empty($sorting)) {
            if (!is_array($sorting)) {
                $sorting = [$sorting];
            }
            $sorting['o.name'] = 'ASC';
        }

        $this->applySorting($queryBuilder, $sorting);

        return $this->getPaginator($queryBuilder, $page, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function create(UuidInterface $id, string $name, array $permissions, bool $readOnly = false) : RoleInterface
    {
        $class = $this->getClassName();

        /** @var RoleInterface $role */
        $role = new $class();
        $role->setId($id);
        $role->setName($name);
        $role->setReadOnly($readOnly);

        foreach ($permissions as $permission) {
            $role->addPermission($permission);
        }

        return $role;
    }

    /**
     * {@inheritdoc}
     */
    public function save(RoleInterface $role) /* : void */
    {
        if (!$this->_em->contains($role)) {
            $this->_em->persist($role);
        }

        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(RoleInterface $role) /* : void */
    {
        $this->_em->remove($role);
        $this->_em->flush();
    }
}