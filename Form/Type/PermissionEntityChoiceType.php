<?php declare(strict_types=1);

namespace TM\RbacBundle\Form\Type;

class PermissionEntityChoiceType extends AbstractEntityChoiceType
{
    /**
     * @param string $permissions
     */
    public function __construct(string $permissions)
    {
        parent::__construct($permissions, 'userPermissions');
    }
}