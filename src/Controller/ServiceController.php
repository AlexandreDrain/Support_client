<?php

namespace App\Controller;

use App\Entity\Service;
use App\Form\ServiceFormType;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/service")
 */
class ServiceController extends AbstractController
{
    /**
     * @Route("/", name="service_index", methods={"GET"})
     */
    public function index(ServiceRepository $serviceRepository): Response
    {
        return $this->render('service/index.html.twig', [
            'services' => $serviceRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="service_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        $service = new Service();
        $form = $this->createForm(ServiceFormType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $service->setCreatedAt(new \Datetime);
            $entityManager->persist($service);
            $entityManager->flush();

            return $this->redirectToRoute('service_index');
        }

        return $this->render('service/new.html.twig', [
            'service' => $service,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="service_show", methods={"GET"})
     */
    public function show(Service $service): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        return $this->render('service/show.html.twig', [
            'service' => $service,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="service_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Service $service): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        $form = $this->createForm(ServiceFormType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('service_index');
        }

        return $this->render('service/edit.html.twig', [
            'service' => $service,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="service_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Service $service): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        if ($this->isCsrfTokenValid('delete'.$service->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($service);
            $entityManager->flush();
        }

        return $this->redirectToRoute('service_index');
    }
}