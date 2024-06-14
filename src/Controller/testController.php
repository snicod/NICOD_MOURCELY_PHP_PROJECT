<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class testController extends AbstractController
{
    #[Route('/test', name: 'app_test')]
    public function register(): Response
    {
        
        return $this->render('base.html.twig', [
            'registrationForm' => 'test',
        ]);
    }
}
