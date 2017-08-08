<?php declare(strict_types=1);

namespace TM\RbacBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractEntityChoiceType extends AbstractType
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $propertyPath;

    /**
     * @param string $className
     * @param string $propertyPath
     */
    public function __construct(string $className, string $propertyPath)
    {
        $this->className = $className;
        $this->propertyPath = $propertyPath;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'class'         => $this->className,
                'property_path' => $this->propertyPath,
                'multiple'      => true,
                'expanded'      => false,
                'required'      => false,
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityType::class;
    }
}