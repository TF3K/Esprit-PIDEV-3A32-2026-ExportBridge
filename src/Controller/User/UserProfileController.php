<?php

namespace App\Controller\User;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Traits\FormValidationTrait;
use App\Service\OAuthStorageService;

#[Route('/dashboard/profile')]
class UserProfileController extends AbstractController
{
    use FormValidationTrait;

    #[Route('', name: 'app_user_profile')]
    public function index(Request $request, EntityManagerInterface $em, OAuthStorageService $oauthStorage): Response
    {
        $manager = $this->getUser();
        $oauthData = $oauthStorage->get($manager->getEmail());
        $error = null;
        $success = null;

        if ($request->isMethod('POST')) {
            $this->clearValidationErrors();

            $firstName = trim($request->request->get('first_name') ?? '');
            $lastName  = trim($request->request->get('last_name') ?? '');
            $email     = trim($request->request->get('email') ?? '');

            // Validate using trait methods
            $this->validateName($firstName, 'First name', true);
            $this->validateName($lastName, 'Last name', true);
            $this->validateEmail($email, 'Email', true);

            // Check if email is already taken by another manager
            if (!$this->hasValidationErrors() && $email !== $manager->getEmail()) {
                $existing = $em->getRepository(\App\Entity\Manager::class)
                    ->findOneBy(['email' => $email]);
                if ($existing && $existing->getId() !== $manager->getId()) {
                    $this->validationErrors[] = 'This email address is already in use.';
                }
            }

            if ($this->hasValidationErrors()) {
                $error = implode(' ', $this->getValidationErrors());
            } else {
                $manager->setFirstName($firstName);
                $manager->setLastName($lastName);
                $manager->setEmail($email);
                $em->flush();
                $success = 'Profile updated successfully.';
            }
        }

        return $this->render('user/profile/index.html.twig', [
            'manager' => $manager,
            'error'   => $error,
            'success' => $success,
            'avatar'  => $oauthData['image_url'] ?? null,
        ]);
    }
}