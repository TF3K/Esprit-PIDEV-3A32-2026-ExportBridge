<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ChatMEsscontrollerController extends AbstractController
{
   #[Route('/chatbot', name: 'app_chatbot')]
    public function index(): Response
    {
        return $this->render('home/home.html.twig', [
            'controller_name' => 'ChatbotController',
        ]);
    }
}
