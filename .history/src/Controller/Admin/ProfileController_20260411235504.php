<?php

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/profile')]
class ProfileController extends AbstractController
{
    #[Route('', name: 'app_admin_profile')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $manager = $this->getUser();
        $error = null;
        $success = null;

        if ($request->isMethod('POST')) {
            $firstName = trim($request->request->get('first_name'));
            $lastName  = trim($request->request->get('last_name'));
            $email     = trim($request->request->get('email'));

            if (empty($firstName) || empty($lastName)) {
                $error = 'First name and last name are required.';
            } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } elseif ($email !== $manager->getEmail()) {
                // Check if email is already taken by another manager
                $existing = $em->getRepository(\App\Entity\Manager::class)
                    ->findOneBy(['email' => $email]);
                if ($existing && $existing->getId() !== $manager->getId()) {
                    $error = 'This email address is already in use.';
                }
            }

            if (!$error) {
                $manager->setFirstName($firstName);
                $manager->setLastName($lastName);
                $manager->setEmail($email);
                $em->flush();
                $success = 'Profile updated successfully.';
            }
        }

        return $this->render('admin/profile/index.html.twig', [
            'manager' => $manager,
            'error'   => $error,
            'success' => $success,
        ]);
    }
}