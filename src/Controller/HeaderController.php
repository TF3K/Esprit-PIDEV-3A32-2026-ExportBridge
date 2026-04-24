<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class HeaderController extends AbstractController
{
    #[Route('/header', name: 'app_header')]
    public function header(TranslatorInterface $translator): Response
    {
        $text = $translator->trans('welcome');

        return $this->render('header/navbarvesitor.html.twig', [
            'controller_name' => 'HeaderController',
            'text' => $text
        ]);
    }


}