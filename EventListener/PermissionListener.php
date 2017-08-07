<?php declare(strict_types=1);

namespace TM\RbacBundle\EventListener;

use Assert\Assertion;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use TM\RbacBundle\Annotation\Permission;
use TM\RbacBundle\Exception\RequestedPermissionIsNotAvailable;

class PermissionListener
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var array|string[]
     */
    private $permissions;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @param Reader $reader
     * @param array|string[] $permissions
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        Reader $reader,
        array $permissions,
        AuthorizationCheckerInterface $authorizationChecker)
    {
        Assertion::allString($permissions);

        $this->reader = $reader;
        $this->permissions = $permissions;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (null === $permission = $this->getPermissionAnnotation($event->getController())) {
            return;
        }

        $this->assertPermissionIsValid($permission->getPermission());

        if (!$this->authorizationChecker->isGranted($permission->getPermission())) {
            throw new AccessDeniedException(sprintf(
                'You do not have the "%s" permission',
                $permission->getPermission()
            ));
        }
    }

    /**
     * @param mixed $controller
     * @return null|Permission
     */
    private function getPermissionAnnotation($controller) /* ?: Permission */
    {
        if (!is_array($controller) && method_exists($controller, '__invoke')) {
            $controller = array($controller, '__invoke');
        }

        if (!is_array($controller)) {
            return null;
        }

        $object = new \ReflectionClass(ClassUtils::getClass($controller[0]));
        $method = $object->getMethod($controller[1]);

        $annotations = $this->reader->getMethodAnnotations($method);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof Permission) {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * @param string $permission
     * @throws RequestedPermissionIsNotAvailable    When requested permission is not found in permissions list
     */
    private function assertPermissionIsValid(string $permission) /* : void */
    {
        if (!array_key_exists($permission, $this->permissions)) {
            throw new RequestedPermissionIsNotAvailable($permission, $this->permissions);
        }
    }
}