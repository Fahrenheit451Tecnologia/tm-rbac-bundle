<?php declare(strict_types=1);

namespace TM\RbacBundle\Form\Type;

class RoleEntityChoiceType extends AbstractEntityChoiceType
{
    /**
     * @param string $className
     */
    public function __construct(string $className)
    {
        parent::__construct($className, 'userRoles');
    }
}