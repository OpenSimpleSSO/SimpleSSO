<?php

namespace App\Controller\Admin;

use App\Form\Admin\UserAccountAttribute\CreateEditUserAccountAttributeType;
use App\Model\UserAccountAttributeModel;
use App\Repository\UserAccountAttributeRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vinorcola\HelperBundle\Controller;

/**
 * @Route("/admin/user-account-attribute", name="admin.userAccountAttribute.")
 */
class UserAccountAttributeController extends Controller
{
    /**
     * @Route("", name="list")
     * @Method("GET")
     *
     * @param UserAccountAttributeRepository $repository
     * @return Response
     */
    public function list(UserAccountAttributeRepository $repository): Response
    {
        $attributes = $repository->findAll();

        return $this->render('Admin/UserAccountAttribute/list.html.twig', [
            'attributes' => $attributes,
        ]);
    }

    /**
     * @Route("/create", name="create")
     * @Method({"GET", "POST"})
     *
     * @param Request                   $request
     * @param UserAccountAttributeModel $model
     * @return Response
     */
    public function create(Request $request, UserAccountAttributeModel $model): Response
    {
        $form = $this->createForm(CreateEditUserAccountAttributeType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $model->create($form->getData());
                $this->saveDatabase();

                return $this->redirectToRoute('admin.userAccountAttribute.list');
            } catch (UniqueConstraintViolationException $exception) {
                $this->addFormError($form['key'], 'userAccountAttribute.key.alreadyUsed');
            }
        }

        return $this->render('Admin/UserAccountAttribute/createEdit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{attributeId}/edit", name="edit", requirements={
     *     "attributeId": "^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$",
     * })
     * @Method({"GET", "POST"})
     *
     * @param Request                        $request
     * @param string                         $attributeId
     * @param UserAccountAttributeRepository $repository
     * @param UserAccountAttributeModel      $model
     * @return Response
     */
    public function edit(
        Request $request,
        string $attributeId,
        UserAccountAttributeRepository $repository,
        UserAccountAttributeModel $model
    ): Response {

        $attribute = $repository->find($attributeId);
        if (!$attribute) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(CreateEditUserAccountAttributeType::class, $model->generateEditionData($attribute));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $model->edit($attribute, $form->getData());
                $this->saveDatabase();

                return $this->redirectToRoute('admin.userAccountAttribute.list');
            } catch (UniqueConstraintViolationException $exception) {
                $this->addFormError($form['key'], 'userAccountAttribute.key.alreadyUsed');
            }
        }

        return $this->render('Admin/UserAccountAttribute/createEdit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
