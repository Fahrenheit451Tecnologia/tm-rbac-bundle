<?php declare(strict_types=1);

namespace TM\RbacBundle\Form\Type;

class PermissionEntityChoiceType extends AbstractEntityChoiceType
{
    /**
     * @param string $className
     */
    public function __construct(string $className)
    {
        parent::__construct($className, 'userPermissions');
    }
}