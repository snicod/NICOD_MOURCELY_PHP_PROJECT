<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\NotificationService;

class EventController extends AbstractController
{
    #[Route('/event', name: 'event_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $events = $entityManager->getRepository(Event::class)->findAll();

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }


    #[Route('/event/new', name: 'event_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventFormType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setCreator($this->getUser());
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/new.html.twig', [
            'eventForm' => $form->createView(),
        ]);
    }

    #[Route('/event/{id}/edit', name: 'event_edit')]
    public function edit(Event $event, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('edit', $event);

        $form = $this->createForm(EventFormType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/edit.html.twig', [
            'eventForm' => $form->createView(),
        ]);
    }

    #[Route('/event/{id}/delete', name: 'event_delete', methods: ['POST'])]
    public function delete(Event $event, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('delete', $event);

        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('event_index');
    }

    #[Route('/event/{id}/subscribe', name: 'event_subscribe')]
    public function subscribe(Event $event, EntityManagerInterface $entityManager, NotificationService $notificationService): Response
    {
        $user = $this->getUser();
        $event->addParticipant($user);
        $entityManager->flush();

        // Envoyer la notification par e-mail
        $notificationService->sendEmail(
            $user->getEmail(),
            $user->getPrenom() . ' ' . $user->getNom(),
            'Confirmation d\'inscription',
            '<p>Vous êtes inscrit à l\'événement : ' . $event->getTitre() . '</p>'
        );

        return $this->redirectToRoute('event_index');
    }

    #[Route('/event/{id}/unsubscribe', name: 'event_unsubscribe')]
    public function unsubscribe(Event $event, EntityManagerInterface $entityManager, NotificationService $notificationService): Response
    {
        $user = $this->getUser();
        $event->removeParticipant($user);
        $entityManager->flush();

        // Envoyer la notification par e-mail
        $notificationService->sendEmail(
            $user->getEmail(),
            $user->getPrenom() . ' ' . $user->getNom(),
            'Annulation d\'inscription',
            '<p>Vous avez annulé votre inscription à l\'événement : ' . $event->getTitre() . '</p>'
        );

        return $this->redirectToRoute('event_index');
    }
    #[Route('/subscriptions', name: 'subscriptions')]
    public function subscriptions(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir cette page.');
        }

        $events = $user->getEvents();

        return $this->render('event/subscriptions.html.twig', [
            'events' => $events,
        ]);
    }
}
