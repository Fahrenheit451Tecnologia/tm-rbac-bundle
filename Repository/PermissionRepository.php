<?php declare(strict_types=1);

namespace TM\RbacBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TM\RbacBundle\Model\PermissionInterface;
use TM\RbacBundle\Repository\Traits\PaginationTrait;

abstract class PermissionRepository extends EntityRepository implements PermissionRepositoryInterface
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
            $sorting['o.id'] = 'ASC';
        }

        $this->applySorting($queryBuilder, $sorting);

        return $this->getPaginator($queryBuilder, $page, $limit);
    }
    
    /**
     * {@inheritdoc}
     */
    public function findByName(string $name) /* : ?PermissionInterface */
    {
        return $this->find($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getByName(string $name) : PermissionInterface
    {
        if (null === $permission = $this->findByName($name)) {
            throw new NotFoundHttpException(sprintf('Permission with name "%s" not found'));
        }

        return $permission;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $key) : PermissionInterface
    {
        $class = $this->getClassName();

        return new $class($key);
    }

    /**
     * {@inheritdoc}
     */
    public function save(PermissionInterface $permission) /* : void */
    {
        if (!$this->_em->contains($permission)) {
            $this->_em->persist($permission);
        }

        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(PermissionInterface $permission) /* : void */
    {
        $this->_em->remove($permission);
        $this->_em->flush();
    }
}