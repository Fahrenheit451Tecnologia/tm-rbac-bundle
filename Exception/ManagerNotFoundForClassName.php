<?php declare(strict_types=1);

namespace TM\RbacBundle\Exception;

class ManagerNotFoundForClassName extends \RuntimeException
{
    /**
     * @param string $className
     */
    public function __construct(string $className)
    {
        parent::__construct(sprintf(
            'Manager can not be found for FQCN "%s"',
            $className
        ));;
    }
}