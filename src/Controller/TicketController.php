<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\Message;
use App\Form\TicketFormType;
use App\Form\MessageFormType;
use App\Service\FileUploader;
use App\Repository\TicketRepository;
use App\Repository\MessageRepository;
use App\Repository\ServiceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/ticket")
 * @Security("is_granted('ROLE_CUSTOMER')")
 */
class TicketController extends AbstractController
{
    /**
     * @Route("/", name="ticket_index", methods={"GET"})
     * @Security("is_granted('ROLE_CUSTOMER')")
     */
    public function index(TicketRepository $ticketRepository, ?UserInterface $user): Response
    {
        return $this->render('ticket/index.html.twig', [
            'tickets' => $this->isGranted($user, "ROLE_CUSTOMER") == ["ROLE_CUSTOMER"] ? $ticketRepository->findBy(["customer" => $user->getCustomer()->getId()]) : $ticketRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="ticket_new", methods={"GET","POST"})
     */
    public function new(Request $request, ?UserInterface $user, ServiceRepository $serviceRepository, FileUploader $fileUploader): Response
    {
        $ticket = new Ticket();
        $form = $this->createForm(TicketFormType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();

            /**
             * @var UploadedFile $brochureFile
             * Permet l'upload de fichier, en suivant les consignes de la doc de symfony : https://symfony.com/doc/current/controller/upload_file.html
             * Avec la mise ne place de la classe Service : FileUploader.
             */
            $brochureFile = $form->get('fileName')->getData();
            // dd($brochureFile);

            if ($brochureFile) {
                $brochureFileName = $fileUploader->upload($brochureFile);
                $ticket->setFilename($brochureFileName);
            }

            $ticket->setAuthor($user);
            $ticket->setCreatedAt(new \Datetime);
            $ticket->setStatut("A valider");
            $ticket->setPriority(0);
            $ticket->setCustomer($user->getCustomer());
            $ticket->addService($serviceRepository->findOneBy(["name" => "Support client"]));
            $entityManager->persist($ticket);
            $entityManager->flush();

            return $this->redirectToRoute('ticket_index');
        }

        return $this->render('ticket/new.html.twig', [
            'ticket' => $ticket,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="ticket_show", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_CUSTOMER')")
     */
    public function show(Request $request, Ticket $ticket, MessageRepository $messageRepository, ?UserInterface $user, FileUploader $fileUploader): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageFormType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();

            $brochureFile = $form->get('fileName')->getData();
            // dd($brochureFile);

            if ($brochureFile) {
                $brochureFileName = $fileUploader->upload($brochureFile);
                $message->setFilename($brochureFileName);
            }

            $message->setAuthor($user);
            $message->setCreatedAt(new \Datetime);
            $message->setTicket($ticket);
            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('ticket_show', ["id" => $ticket->getId()]);

        }

        return $this->render('ticket/show.html.twig', [
            'message' => $message,
            'ticket' => $ticket,
            'form' => $form->createView(),
            'messages' => $messageRepository->findBy(["ticket" => $ticket->getId()])
        ]);
    }

    /**
     * @Route("/{id}/edit", name="ticket_edit", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function edit(Request $request, Ticket $ticket): Response
    {
        $form = $this->createForm(TicketFormType::class, $ticket, ['modify_ticket' => 'modify']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $ticket->setUpdatedAt(new \Datetime);
            $entityManager->persist($ticket);
            $entityManager->flush();

            return $this->redirectToRoute('ticket_index');
        }

        return $this->render('ticket/edit.html.twig', [
            'ticket' => $ticket,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="ticket_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Ticket $ticket): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ticket->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($ticket);
            $entityManager->flush();
        }

        return $this->redirectToRoute('ticket_index');
    }

    /**
     * @Route("/{id}/message", name="ticket_new_message", methods={"GET","POST"})
     */
    public function newMessage(Request $request, Ticket $ticket, ?UserInterface $user, FileUploader $fileUploader): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageFormType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();

            $brochureFile = $form->get('fileName')->getData();
            // dd($brochureFile);

            if ($brochureFile) {
                $brochureFileName = $fileUploader->upload($brochureFile);
                $message->setFilename($brochureFileName);
            }

            $message->setAuthor($user);
            $message->setCreatedAt(new \Datetime);
            $message->setTicket($ticket);
            $entityManager->persist($message);
            $entityManager->flush();
            return $this->redirectToRoute('ticket_show', [$ticket->getId()]);
        }

        return $this->render('ticket/new.html.twig', [
        ]);
    }
}