<?php declare(strict_types=1);

namespace TM\RbacBundle\Controller;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TM\RbacBundle\Model\PermissionInterface;
use TM\RbacBundle\Repository\RepositoryProvider;
use TM\RbacBundle\Controller\Traits\AssertMethodIsAllowedTrait;

class PermissionController
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
     * @return Response
     */
    public function listAction(Request $request) : Response
    {
        $this->assertMethodIsAllowed($request, Request::METHOD_GET);

        return $this->viewHandler->handle(View::create($this->repositoryProvider->getPermissionRepository()->findAll()));
    }

    /**
     * @param Request $request
     * @param mixed $id
     * @return Response
     */
    public function readAction(Request $request, $id) : Response
    {
        $this->assertMethodIsAllowed($request, Request::METHOD_GET);

        return $this->viewHandler->handle(View::create($this->findOr404($id)));
    }


    /**
     * @param mixed $id
     * @return PermissionInterface
     */
    private function findOr404($id) : PermissionInterface
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
}