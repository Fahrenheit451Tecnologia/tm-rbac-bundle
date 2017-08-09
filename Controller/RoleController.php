<?php declare(strict_types=1);

namespace TM\RbacBundle\Controller;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TM\RbacBundle\Form\Type\RoleType;
use TM\RbacBundle\Model\RoleInterface;
use TM\RbacBundle\Repository\RepositoryProvider;
use TM\RbacBundle\Controller\Traits\AssertMethodIsAllowedTrait;

class RoleController
{
    use AssertMethodIsAllowedTrait;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var RepositoryProvider
     */
    private $repositoryProvider;

    /**
     * @var ViewHandlerInterface
     */
    private $viewHandler;

    /**
     * @param FormFactoryInterface $formFactory
     * @param RepositoryProvider $repositoryProvider
     * @param ViewHandlerInterface $viewHandler
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        RepositoryProvider $repositoryProvider,
        ViewHandlerInterface $viewHandler
    ) {
        $this->formFactory = $formFactory;
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

        return $this->viewHandler->handle(View::create($this->repositoryProvider->getRoleRepository()->findAll()));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function createAction(Request $request) : Response
    {
        $this->assertMethodIsAllowed($request,Request::METHOD_POST);

        $roleRepository = $this->repositoryProvider->getRoleRepository();

        $role = $roleRepository->createNew();
        $form = $this->formFactory->createNamed('', RoleType::class);

        if (!$form->submit($request->request->all())->isValid()) {
            return $this->viewHandler->handle(View::create($form));
        }

        $roleRepository->save($role);

        return $this->viewHandler->handle(View::create($role, Response::HTTP_CREATED));
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
     * @param Request $request
     * @param mixed $id
     * @return Response
     */
    public function updateAction(Request $request, $id) : Response
    {
        $this->assertMethodIsAllowed($request, [
            Request::METHOD_PUT,
            Request::METHOD_PATCH,
        ]);

        $role = $this->findOr404($id);
        $form = $this->formFactory->createNamed('', RoleType::class);

        if (!$form->submit($request->request->all())->isValid()) {
            return $this->viewHandler->handle(View::create($form));
        }

        $this->repositoryProvider->getRoleRepository()->save($role);

        return $this->viewHandler->handle(View::create($role, Response::HTTP_OK));
    }

    /**
     * @param Request $request
     * @param mixed $id
     * @return Response
     */
    public function deleteAction(Request $request, $id) : Response
    {
        $this->assertMethodIsAllowed($request, Request::METHOD_DELETE);

        $role = $this->findOr404($id);

        if ($role->isReadOnly()) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, sprintf(
                'Role with id "%s" is read-only so can only be deleted on the command line',
                $id
            ));
        }

        $this->repositoryProvider->getRoleRepository()->delete($role);

        return $this->viewHandler->handle(View::create(null, Response::HTTP_NO_CONTENT));
    }

    /**
     * @param mixed $id
     * @return RoleInterface
     */
    private function findOr404($id) : RoleInterface
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