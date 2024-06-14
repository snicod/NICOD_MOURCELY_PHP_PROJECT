<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

    #[Route('/event/{id}/subscribe', name: 'event_subscribe', methods: ['POST'])]
    public function subscribe(Event $event, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('subscribe'.$event->getId(), $request->request->get('_token')) && $event->getParticipants()->count() < $event->getNbParticipantMax() && !$event->getParticipants()->contains($user)) {
            $event->addParticipant($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('event_index');
    }

    #[Route('/event/{id}/unsubscribe', name: 'event_unsubscribe', methods: ['POST'])]
    public function unsubscribe(Event $event, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('unsubscribe'.$event->getId(), $request->request->get('_token')) && $event->getParticipants()->contains($user)) {
            $event->removeParticipant($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('event_index');
    }

    #[Route('/subscriptions', name: 'event_subscriptions')]
    public function subscriptions(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $events = $user->getEvents();

        return $this->render('event/subscriptions.html.twig', [
            'events' => $events,
        ]);
    }
}
