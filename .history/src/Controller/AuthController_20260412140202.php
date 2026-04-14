<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Manager;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Traits\FormValidationTrait;

class AuthController extends AbstractController
{
    use FormValidationTrait;

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('app_admin_dashboard');
            }
            return $this->redirectToRoute('app_dashboard');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        CompanyRepository $companyRepo
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_redirect');
        }

        $errors = [];
        $old    = [];

        if ($request->isMethod('POST')) {
            $firstName       = trim($request->request->get('first_name') ?? '');
            $lastName        = trim($request->request->get('last_name') ?? '');
            $email           = trim($request->request->get('email') ?? '');
            $password        = $request->request->get('password') ?? '';
            $confirmPassword = $request->request->get('confirm_password') ?? '';
            $companyId       = $request->request->get('company_id');

            // Keep old values so form repopulates on error (never repopulate passwords)
            $old = [
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'email'      => $email,
            ];

            // --- Validate each field individually ---

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

            // Only check for duplicate email if the format is valid
            if (!isset($errors['email'])) {
                $existingManager = $em->getRepository(Manager::class)->findOneBy(['email' => $email]);
                if ($existingManager) {
                    $errors['email'] = 'This email address is already registered.';
                }
            }

            $this->validatePassword($password, true);
            if ($this->hasValidationErrors()) {
                $errors['password'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validatePasswordMatch($password, $confirmPassword);
            if ($this->hasValidationErrors()) {
                $errors['confirm_password'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            // --- Only persist if no errors ---
            if (empty($errors)) {
                $manager = new Manager();
                $manager->setFirstName($firstName);
                $manager->setLastName($lastName);
                $manager->setEmail($email);
                $manager->setRoles(['ROLE_USER']);
                $manager->setPassword($hasher->hashPassword($manager, $password));
                $manager->setCreatedAt(new \DateTime());
                $manager->setCompany($companyId ? $companyRepo->find($companyId) : null);

                $em->persist($manager);
                $em->flush();

                $this->addFlash('success', 'Account created! You can now log in.');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('auth/register.html.twig', [
            'errors'    => $errors,
            'old'       => $old,
            'companies' => $companyRepo->findAll(),
        ]);
    }

    #[Route('/redirect', name: 'app_redirect')]
    public function redirectAfterLogin(): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_admin_dashboard');
        }

        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/admin/ping', name: 'app_admin_ping')]
    public function ping(): Response
    {
        if (!$this->getUser()) {
            return new Response('', 401);
        }
        return new Response('', 200);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony intercepts this automatically
    }
}