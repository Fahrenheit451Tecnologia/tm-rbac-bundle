<?php declare(strict_types=1);

namespace TM\RbacBundle\Form\Type;

use TM\RbacBundle\Form\AbstractEntityChoiceType;

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