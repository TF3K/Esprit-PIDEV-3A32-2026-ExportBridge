<?php

namespace App\Controller\Admin;

use App\Entity\Manager;
use App\Repository\ManagerRepository;
use App\Repository\CompanyRepository;
use App\Service\BreachedPasswordService;
use App\Service\EmailValidationService;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Traits\FormValidationTrait;

#[Route('/admin/managers')]
class ManagerController extends AbstractController
{
    use FormValidationTrait;

    #[Route('', name: 'app_admin_managers')]
    public function index(ManagerRepository $repo): Response
    {
        return $this->render('admin/managers/index.html.twig', [
            'managers' => $repo->findNonAdmins(),
        ]);
    }

    #[Route('/add', name: 'app_admin_managers_add')]
    public function add(
        Request $request,
        EntityManagerInterface $em,
        CompanyRepository $companyRepo,
        UserPasswordHasherInterface $hasher,
        EmailValidationService $emailValidationService,
        BreachedPasswordService $breachedPasswordService,
        NotificationService $notificationService,
    ): Response {
        $manager = new Manager();
        $errors = [];
        $old    = [];

        // dd('ADD', $request->getMethod());

        // dump('method: ' . $request->getMethod());

        if ($request->isMethod('POST')) {
            // dump('entered POST block');
            $firstName = trim($request->request->get('first_name') ?? '');
            $lastName  = trim($request->request->get('last_name') ?? '');
            $email     = trim($request->request->get('email') ?? '');
            $password  = $request->request->get('password') ?? '';
            $companyId = $request->request->get('company_id');
            $role      = $request->request->get('role');

            $old = [
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'email'      => $email,
                'role'       => $role,
                'company_id' => $companyId,
            ];

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

            // dd($errors);

            $this->validateEmail($email, 'Email', true);
            if ($this->hasValidationErrors()) {
                $errors['email'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            if (!isset($errors['email'])) {
                $existing = $em->getRepository(Manager::class)->findOneBy(['email' => $email]);
                if ($existing) {
                    $errors['email'] = 'This email address is already in use.';
                }
            }

            if (!isset($errors['email'])) {
                $emailValidationError = $emailValidationService->getRejectionReason($email);
                if ($emailValidationError !== null) {
                    $errors['email'] = $emailValidationError;
                }
            }

            $this->validatePassword($password, true);
            if ($this->hasValidationErrors()) {
                $errors['password'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            if (!isset($errors['password'])) {
                $breachCount = $breachedPasswordService->getBreachCount($password);
                if ($breachCount > 0) {
                    $errors['password'] = 'This password has appeared in known data breaches. Please choose a different password.';
                }
            }

            if (empty($errors)) {
                $manager->setFirstName($firstName);
                $manager->setLastName($lastName);
                $manager->setEmail($email);
                $manager->setRoles($role === 'ROLE_ADMIN' ? ['ROLE_ADMIN'] : ['ROLE_USER']);
                $manager->setPassword($hasher->hashPassword($manager, $password));
                $manager->setCreatedAt(new \DateTime());
                $manager->setCompany($companyId ? $companyRepo->find($companyId) : null);

                $em->persist($manager);
                $em->flush();

                $notificationService->createForManager(
                    $manager,
                    'account',
                    'Your account was created by an administrator.'
                );

                $this->addFlash('success', 'Manager added successfully.');
                return $this->redirectToRoute('app_admin_managers');
            }
        }

        return $this->render('admin/managers/form.html.twig', [
            'manager'   => $manager,
            'companies' => $companyRepo->findAll(),
            'mode'      => 'add',
            'errors'    => $errors,
            'old'       => $old,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_managers_edit', requirements: ['id' => '\d+'])]
    public function edit(
        Manager $manager,
        Request $request,
        EntityManagerInterface $em,
        CompanyRepository $companyRepo,
        UserPasswordHasherInterface $hasher,
        EmailValidationService $emailValidationService,
        BreachedPasswordService $breachedPasswordService,
        NotificationService $notificationService,
    ): Response {
        $errors = [];
        $old    = [];

        // dd('EDIT', $request->getMethod(), $manager->getId());

        if ($request->isMethod('POST')) {
            $firstName = trim($request->request->get('first_name') ?? '');
            $lastName  = trim($request->request->get('last_name') ?? '');
            $email     = trim($request->request->get('email') ?? '');
            $password  = $request->request->get('password') ?? '';
            $companyId = $request->request->get('company_id');
            $role      = $request->request->get('role');

            $old = [
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'email'      => $email,
                'role'       => $role,
                'company_id' => $companyId,
            ];

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

            // Exclude the current manager from the duplicate check
            if (!isset($errors['email'])) {
                $existing = $em->getRepository(Manager::class)->findOneBy(['email' => $email]);
                if ($existing && $existing->getId() !== $manager->getId()) {
                    $errors['email'] = 'This email address is already in use.';
                }
            }

            if (!isset($errors['email'])) {
                $emailValidationError = $emailValidationService->getRejectionReason($email);
                if ($emailValidationError !== null) {
                    $errors['email'] = $emailValidationError;
                }
            }

            // Password is optional on edit — only validate if provided
            if ($password !== '') {
                $this->validatePassword($password, false);
                if ($this->hasValidationErrors()) {
                    $errors['password'] = $this->getFirstValidationError();
                    $this->clearValidationErrors();
                }

                if (!isset($errors['password'])) {
                    $breachCount = $breachedPasswordService->getBreachCount($password);
                    if ($breachCount > 0) {
                        $errors['password'] = 'This password has appeared in known data breaches. Please choose a different password.';
                    }
                }
            }

            if (empty($errors)) {
                $manager->setFirstName($firstName);
                $manager->setLastName($lastName);
                $manager->setEmail($email);
                $manager->setRoles($role === 'ROLE_ADMIN' ? ['ROLE_ADMIN'] : ['ROLE_USER']);
                $manager->setCompany($companyId ? $companyRepo->find($companyId) : null);

                if ($password !== '') {
                    $manager->setPassword($hasher->hashPassword($manager, $password));
                }

                $em->flush();

                $notificationService->createForManager(
                    $manager,
                    'profile',
                    'Your manager profile was updated by an administrator.'
                );

                $this->addFlash('success', 'Manager updated successfully.');
                return $this->redirectToRoute('app_admin_managers');
            }
        }

        return $this->render('admin/managers/form.html.twig', [
            'manager'   => $manager,
            'companies' => $companyRepo->findAll(),
            'mode'      => 'edit',
            'errors'    => $errors,
            'old'       => $old,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_managers_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Manager $manager, EntityManagerInterface $em): Response
    {
        $em->remove($manager);
        $em->flush();

        $this->addFlash('success', 'Manager deleted.');
        return $this->redirectToRoute('app_admin_managers');
    }
}