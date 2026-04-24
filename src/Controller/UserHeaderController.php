<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
final class UserHeaderController extends AbstractController
{
    #[Route('/user/header', name: 'app_user_header')]
    public function index(TranslatorInterface $translator): Response
    {
         $text = $translator->trans('welcome');
        return $this->render('user/partials/navbar.html.twig', [
            'controller_name' => 'UserHeaderController',
             'text' => $text
        ]);
    }
    
}
