<?php declare(strict_types=1);

namespace TM\RbacBundle\Model;

interface PermissionInterface
{
    /**
     * Get id
     *
     * @return string
     */
    public function getId();

    /**
     * Get permission name
     *
     * @return string
     */
    public function getName() : string;
}