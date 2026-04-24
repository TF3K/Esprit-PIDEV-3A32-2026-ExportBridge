<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FooterController extends AbstractController
{
    #[Route('/footer', name: 'app_footer')]
    public function index(): Response
    {
        return $this->render('footer/footer.html.twig', [
            'controller_name' => 'FooterController',

'emailjs_public_key' => $_ENV['EMAILJS_PUBLIC_KEY'],
            'emailjs_service_id' => $_ENV['EMAILJS_SERVICE_ID'],
            'emailjs_template_id' => $_ENV['EMAILJS_TEMPLATE_ID'],
        ]);
    }
}