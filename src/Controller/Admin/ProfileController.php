<?php

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Traits\FormValidationTrait;

#[Route('/admin/profile')]
class ProfileController extends AbstractController
{
    use FormValidationTrait;

    #[Route('', name: 'app_admin_profile')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $manager = $this->getUser();
        $errors  = [];
        $success = null;

        if ($request->isMethod('POST')) {
            $firstName = trim($request->request->get('first_name') ?? '');
            $lastName  = trim($request->request->get('last_name') ?? '');
            $email     = trim($request->request->get('email') ?? '');

            $this->clearValidationErrors();

            $this->validateName($firstName, 'First name', true);
            if ($this->hasValidationErrors()) {
                $errors['first_name'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateName($lastName, 'Last name', true);
            if ($this->hasValidationErrors()) {
                $errors['last_name'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateEmail($email, 'Email', true);
            if ($this->hasValidationErrors()) {
                $errors['email'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            if (!isset($errors['email']) && $email !== $manager->getEmail()) {
                $existing = $em->getRepository(\App\Entity\Manager::class)->findOneBy(['email' => $email]);
                if ($existing && $existing->getId() !== $manager->getId()) {
                    $errors['email'] = 'This email address is already in use.';
                }
            }

            if (empty($errors)) {
                $manager->setFirstName($firstName);
                $manager->setLastName($lastName);
                $manager->setEmail($email);
                $em->flush();
                $success = 'Profile updated successfully.';
            }
        }

        return $this->render('admin/profile/index.html.twig', [
            'manager' => $manager,
            'errors'  => $errors,
            'success' => $success,
        ]);
    }
}