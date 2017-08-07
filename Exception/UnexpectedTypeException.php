<?php declare(strict_types=1);

namespace TMRbacBundle\Exception;

class UnexpectedTypeException extends \Exception
{
    /**
     * @param mixed $value
     * @param string $expected
     */
    public function __construct($value, string $expected)
    {
        parent::__construct(sprintf(
            'Argument expected to be of type "%s" but "%s" was given',
            $expected,
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }
}