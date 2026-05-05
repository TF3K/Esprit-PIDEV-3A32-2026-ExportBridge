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

class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $user = $this->getUser();

        if ($user instanceof Manager) {
            $userId = $user->getId();

            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('app_admin_dashboard', ['id' => $userId]);
            }

            return $this->redirectToRoute('app_dashboard', ['id' => $userId]);
        }

        // Récupère l'erreur si elle existe
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupère le dernier identifiant (email) saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername, // On garde le nom standard Symfony pour le formulaire
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
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');
            $firstName = $request->request->get('first_name');
            $lastName = $request->request->get('last_name');
            $companyId = $request->request->get('company_id');

            if ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters.';
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
