<?php declare(strict_types=1);

namespace TM\RbacBundle\Controller\Traits;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use TMRbacBundle\Exception\UnexpectedTypeException;

trait AssertMethodIsAllowedTrait
{
    /**
     * @param Request $request
     * @param array|string[]|string $methods
     * @throws UnexpectedTypeException
     */
    protected function assertMethodIsAllowed(Request $request, $methods)
    {
        if (!is_array($methods) && !is_string($methods)) {
            throw new UnexpectedTypeException($methods, 'array or string');
        }

        if (!is_array($methods)) {
            $methods = [$methods];
        }

        if (!in_array($request->getMethod(), $methods)) {
            throw new MethodNotAllowedHttpException($methods);
        }
    }
}