<?php declare(strict_types=1);

namespace TM\RbacBundle\Exception;

class AnnotationHasNoPermissionSet extends \RuntimeException
{
    /**
     */
    public function __construct()
    {
        parent::__construct('The @Permission annotation must have a value set');
    }
}