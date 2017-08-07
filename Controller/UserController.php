<?php declare(strict_types=1);

namespace TM\RbacBundle\Controller;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TM\RbacBundle\Model\PermissionInterface;
use TM\RbacBundle\Model\RoleInterface;
use TM\RbacBundle\Model\UserInterface;
use TM\RbacBundle\Repository\RepositoryProvider;
use TM\RbacBundle\Controller\Traits\AssertMethodIsAllowedTrait;

class UserController
{
    use AssertMethodIsAllowedTrait;

    /**
     * @var RepositoryProvider
     */
    private $repositoryProvider;

    /**
     * @var ViewHandlerInterface
     */
    private $viewHandler;

    /**
     * @param RepositoryProvider $repositoryProvider
     * @param ViewHandlerInterface $viewHandler
     */
    public function __construct(
        RepositoryProvider $repositoryProvider,
        ViewHandlerInterface $viewHandler
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->viewHandler = $viewHandler;
    }

    /**
     * @param Request $request
     * @param mixed $permissionId
     * @return Response
     */
    public function listUsersWithPermission(Request $request, $permissionId) : Response
    {
        $this->assertMethodIsAllowed($request, Request::METHOD_GET);

        $paginator = $this->repositoryProvider->getUserRepository()
            ->createUserWithPermissionPaginator($this->findPermissionOr404($permissionId));

        return $this->viewHandler->handle(View::create($paginator));
    }

    /**
     * @param Request $request
     * @param mixed $id
     * @param mixed $permissionId
     * @return Response
     */
    public function addPermissionToUser(Request $request, $id, $permissionId) : Response
    {
        $this->assertMethodIsAllowed($request,Request::METHOD_POST);

        $user = $this->findUserOr404($id);
        $permission = $this->findPermissionOr404($permissionId);

        if ($user->hasUserPermission($permission)) {
            return $this->viewHandler->handle(View::create(null, Response::HTTP_NOT_MODIFIED));
        }

        $user->addUserPermission($permission);
        $this->repositoryProvider->getUserRepository()->save($user);

        return $this->viewHandler->handle(View::create($user, Response::HTTP_OK));
    }

    /**
     * @param Request $request
     * @param mixed $id
     * @param mixed $permissionId
     * @return Response
     */
    public function removePermissionFromUser(Request $request, $id, $permissionId) : Response
    {
        $this->assertMethodIsAllowed($request,Request::METHOD_DELETE);

        $user = $this->findUserOr404($id);
        $permission = $this->findPermissionOr404($permissionId);

        if (!$user->hasUserPermission($permission)) {
            return $this->viewHandler->handle(View::create(null, Response::HTTP_NOT_MODIFIED));
        }

        $user->removeUserPermission($permission);
        $this->repositoryProvider->getUserRepository()->save($user);

        return $this->viewHandler->handle(View::create($user, Response::HTTP_OK));
    }

    /**
     * @param Request $request
     * @param mixed $roleId
     * @return Response
     */
    public function listUsersWithRole(Request $request, $roleId) : Response
    {
        $this->assertMethodIsAllowed($request, Request::METHOD_GET);

        $paginator = $this->repositoryProvider->getUserRepository()
            ->createUserWithRolePaginator($this->findRoleOr404($roleId));

        return $this->viewHandler->handle(View::create($paginator));
    }

    /**
     * @param Request $request
     * @param mixed $id
     * @param mixed $roleId
     * @return Response
     */
    public function addRoleToUser(Request $request, $id, $roleId) : Response
    {
        $this->assertMethodIsAllowed($request,Request::METHOD_POST);

        $user = $this->findUserOr404($id);
        $role = $this->findRoleOr404($roleId);

        if ($user->hasUserRole($role)) {
            return $this->viewHandler->handle(View::create(null, Response::HTTP_NOT_MODIFIED));
        }

        $user->addUserRole($role);
        $this->repositoryProvider->getUserRepository()->save($user);

        return $this->viewHandler->handle(View::create($user, Response::HTTP_OK));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function removeRoleFromUser(Request $request, $id, $roleId)
    {
        $this->assertMethodIsAllowed($request,Request::METHOD_DELETE);

        $user = $this->findUserOr404($id);
        $role = $this->findRoleOr404($roleId);

        if (!$user->hasUserRole($role)) {
            return $this->viewHandler->handle(View::create(null, Response::HTTP_NOT_MODIFIED));
        }

        $user->removeUserRole($role);
        $this->repositoryProvider->getUserRepository()->save($user);

        return $this->viewHandler->handle(View::create($user, Response::HTTP_OK));
    }

    /**
     * @param mixed $id
     * @return UserInterface
     */
    private function findUserOr404($id) : UserInterface
    {
        /** @var UserInterface $user */
        if (null === $user = $this->repositoryProvider->getUserRepository()->find($id)) {
            throw new NotFoundHttpException(sprintf(
                'User with id "%s" can not be found',
                $id
            ));
        }

        return $user;
    }

    /**
     * @param mixed $id
     * @return PermissionInterface
     */
    private function findPermissionOr404($id) : PermissionInterface
    {
        /** @var PermissionInterface $permission */
        if (null === $permission = $this->repositoryProvider->getPermissionRepository()->find($id)) {
            throw new NotFoundHttpException(sprintf(
                'Permission with id "%s" can not be found',
                $id
            ));
        }

        return $permission;
    }

    /**
     * @param mixed $id
     * @return RoleInterface
     */
    private function findRoleOr404($id) : RoleInterface
    {
        /** @var RoleInterface $role */
        if (null === $role = $this->repositoryProvider->getRoleRepository()->find($id)) {
            throw new NotFoundHttpException(sprintf(
                'Role with id "%s" can not be found',
                $id
            ));
        }

        return $role;
    }
}