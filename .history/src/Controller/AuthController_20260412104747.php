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
            'error' => $error,
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

        $error = null;

        if ($request->isMethod('POST')) {
            $email = trim($request->request->get('email') ?? '');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');
            $firstName = trim($request->request->get('first_name') ?? '');
            $lastName = trim($request->request->get('last_name') ?? '');
            $companyId = $request->request->get('company_id');

            // Server-side validation
            $this->clearValidationErrors();
            $errors = [];
            
            $this->validateName($firstName, 'First name', true);
            if ($this->hasValidationErrors()) {
                $errors['first_name'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }
            $this->validateName($lastName, 'Last name', true);
            $this->validateEmail($email, 'Email', true);
            $this->validatePassword($password, true);
            $this->validatePasswordMatch($password, $confirmPassword);

            // Check if email already exists
            if (!$this->validateEmail($email, 'Email', true)) {
                $existingManager = $em->getRepository(Manager::class)->findOneBy(['email' => $email]);
                if ($existingManager) {
                    $this->validationErrors[] = 'This email address is already registered.';
                }
            }

            if ($this->hasValidationErrors()) {
                $error = implode(' ', $this->getValidationErrors());
            } else {
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
            'error'     => $error,
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

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony intercepts this automatically, method body never executes
    }
}