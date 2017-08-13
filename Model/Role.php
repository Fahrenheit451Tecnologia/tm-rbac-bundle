<?php declare(strict_types=1);

namespace TM\RbacBundle\Model;

use Assert\Assertion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

abstract class Role implements RoleInterface
{
    /**
     * @var integer|string|mixed
     */
    protected $id;
    
    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    private $readOnly = false;

    /**
     * @var ArrayCollection|PermissionInterface[]
     */
    protected $permissions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->permissions = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId() /* : mixed */
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name) /* : void */
    {
        Assertion::betweenLength($name, 2, 255);

        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName() /* : string */
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setReadOnly(bool $readOnly) /* : void */
    {
        $this->readOnly = $readOnly;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadOnly() : bool
    {
        return true === $this->readOnly;
    }

    /**
     * {@inheritdoc}
     */
    public function hasPermission(PermissionInterface $permission) : bool
    {
        return $this->permissions->contains($permission);
    }
    
    /**
     * {@inheritdoc}
     */
    public function addPermission(PermissionInterface $permission) : RoleInterface
    {
        if (!$this->hasPermission($permission)) {
            $this->permissions->add($permission);
        }
        
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function removePermission(PermissionInterface $permission) : RoleInterface
    {
        if ($this->hasPermission($permission)) {
            $this->permissions->removeElement($permission);
        }
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions() : Collection
    {
        return $this->permissions;
    }
}