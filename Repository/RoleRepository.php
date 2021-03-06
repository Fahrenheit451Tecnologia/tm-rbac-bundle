<?php declare(strict_types=1);

namespace TM\RbacBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TM\RbacBundle\Model\RoleInterface;
use TM\RbacBundle\Model\UserInterface;
use TM\RbacBundle\Repository\Traits\PaginationTrait;

class RoleRepository extends EntityRepository implements RoleRepositoryInterface
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
    public function createNew() : RoleInterface
    {
        $className = $this->getClassName();

        return new $className();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByName(string $name) /* : ?RoleInterface */
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getOneByName(string $name) : RoleInterface
    {
        if (null === $permission = $this->findOneByName($name)) {
            throw new NotFoundHttpException(sprintf('Role with name "%s" not found'));
        }

        return $permission;
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

    /**
     * {@inheritdoc}
     */
    public function findAllRolesNotUsedByUser(UserInterface $user) : array
    {
        $queryBuilder = $this->createQueryBuilder('r');

        if (!$user->getUserRoles()->isEmpty()) {
            $queryBuilder
                ->where($queryBuilder->expr()->notIn('r.name', ':names'))
                ->setParameter('names', array_map(function (RoleInterface $role) {
                    return $role->getName();
                }, $user->getUserRoles()->toArray()))
            ;
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }
}