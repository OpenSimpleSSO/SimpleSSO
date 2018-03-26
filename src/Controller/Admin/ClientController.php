<?php

namespace App\Controller\Admin;

use App\Form\Admin\Client\CreateEditClientType;
use App\Form\Admin\Client\DeleteClientType;
use App\Model\ClientModel;
use App\Repository\ClientRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vinorcola\HelperBundle\Controller;

/**
 * @Route("/admin/client", name="admin.client.")
 */
class ClientController extends Controller
{
    /**
     * @Route("", name="list")
     * @Method("GET")
     *
     * @param ClientRepository $repository
     * @return Response
     */
    public function list(ClientRepository $repository): Response
    {
        $clients = $repository->findAll();

        return $this->render('Admin/Client/list.html.twig', [
            'clients' => $clients,
        ]);
    }

    /**
     * @Route("/create", name="create")
     * @Method({"GET", "POST"})
     *
     * @param Request     $request
     * @param ClientModel $model
     * @return Response
     */
    public function create(Request $request, ClientModel $model): Response
    {
        $form = $this->createForm(CreateEditClientType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $model->create($form->getData());
            $this->saveDatabase();

            return $this->redirectToRoute('admin.client.list');
        }

        return $this->render('Admin/Client/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{clientId}/edit", name="edit", requirements={
     *     "clientId": "^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$",
     * })
     * @Method({"GET", "POST"})
     *
     * @param Request          $request
     * @param string           $clientId
     * @param ClientRepository $repository
     * @param ClientModel      $model
     * @return Response
     */
    public function edit(
        Request $request,
        string $clientId,
        ClientRepository $repository,
        ClientModel $model
    ): Response {

        $client = $repository->find($clientId);
        if (!$client) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(CreateEditClientType::class, $model->generateEditionData($client));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $model->edit($client, $form->getData());
            $this->saveDatabase();

            return $this->redirectToRoute('admin.client.list');
        }

        return $this->render('Admin/Client/edit.html.twig', [
            'client' => $client,
            'form'   => $form->createView(),
        ]);
    }

    /**
     * @Route("/{clientId}/delete", name="delete", requirements={
     *     "clientId": "^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$",
     * })
     * @Method({"GET", "DELETE"})
     *
     * @param Request          $request
     * @param string           $clientId
     * @param ClientRepository $repository
     * @param ClientModel      $model
     * @return Response
     */
    public function delete(
        Request $request,
        string $clientId,
        ClientRepository $repository,
        ClientModel $model
    ): Response {

        $client = $repository->find($clientId);
        if (!$client) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(DeleteClientType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $model->delete($client);
            $this->saveDatabase();

            return $this->redirectToRoute('admin.client.list');
        }

        return $this->render('Admin/Client/delete.html.twig', [
            'client' => $client,
            'form'   => $form->createView(),
        ]);
    }
}
