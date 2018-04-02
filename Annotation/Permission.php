<?php declare(strict_types=1);

namespace TM\RbacBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;
use TM\RbacBundle\Exception\RbacException;
use TM\RbacBundle\TMPermissions;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Permission extends Annotation
{
    /**
     * @return string
     * @throws RbacException
     */
    public function getPermission() : string
    {
        if (null === $this->value) {
            throw RbacException::annotationHasNoPermissionSet();
        }

        if (!TMPermissions::getInstance()->isPermission($this->value)) {
            throw RbacException::annotationHasInvalidPermissionSet($this->value);
        }

        return $this->value;
    }
}