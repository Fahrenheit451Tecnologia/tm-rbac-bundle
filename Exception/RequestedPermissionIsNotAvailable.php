<?php declare(strict_types=1);

namespace TM\RbacBundle\Exception;

class RequestedPermissionIsNotAvailable extends \RuntimeException
{
    /**
     * @param string $requested
     * @param array $permissions
     */
    public function __construct(string $requested, array $permissions)
    {
        parent::__construct(sprintf(
            'Requested permission "%s" is not in the list of available permissions "%s"',
            $requested,
            implode('", "', array_keys($permissions))
        ));
    }
}