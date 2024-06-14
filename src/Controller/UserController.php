<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/subscriptions', name: 'user_subscriptions')]
    public function subscriptions(): Response
    {
        // Logic to get user subscriptions
        $user = $this->getUser();
        $events = []; // Fetch events the user is subscribed to

        return $this->render('user/subscriptions.html.twig', [
            'events' => $events,
        ]);
    }
}
