<?php declare(strict_types=1);

namespace TM\RbacBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;
use TM\RbacBundle\Exception\AnnotationHasNoPermissionSet;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Permission extends Annotation
{
    /**
     * @return string
     * @throws AnnotationHasNoPermissionSet
     */
    public function getPermission() : string
    {
        if (null === $this->value) {
            throw new AnnotationHasNoPermissionSet();
        }

        return $this->value;
    }
}