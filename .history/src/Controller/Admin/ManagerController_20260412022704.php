<?php

namespace App\Controller\Admin;

use App\Entity\Manager;
use App\Repository\ManagerRepository;
use App\Repository\CompanyRepository;
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
            'managers' => $repo->findAll(),
        ]);
    }

    #[Route('/add', name: 'app_admin_managers_add')]
    public function add(
        Request $request,
        EntityManagerInterface $em,
        CompanyRepository $companyRepo,
        UserPasswordHasherInterface $hasher
    ): Response {
        $manager = new Manager();

        if ($request->isMethod('POST')) {
            $this->clearValidationErrors();

            $firstName = trim($request->request->get('first_name') ?? '');
            $lastName = trim($request->request->get('last_name') ?? '');
            $email = trim($request->request->get('email') ?? '');
            $password = $request->request->get('password');
            $companyId = $request->request->get('company_id');
            $role = $request->request->get('role');

            // Validate required fields
            $this->validateName($firstName, 'First name', true);
            $this->validateName($lastName, 'Last name', true);
            $this->validateEmail($email, 'Email', true);
            $this->validatePassword($password, true);

            // Check if email already exists
            $existingManager = $em->getRepository(Manager::class)->findOneBy(['email' => $email]);
            if ($existingManager) {
                $this->validationErrors[] = 'This email address is already in use.';
            }

            if ($this->hasValidationErrors()) {
                return $this->render('admin/managers/form.html.twig', [
                    'manager'   => $manager,
                    'companies' => $companyRepo->findAll(),
                    'mode'      => 'add',
                    'error'     => implode(' ', $this->getValidationErrors()),
                ]);
            }

            $manager->setFirstName($firstName);
            $manager->setLastName($lastName);
            $manager->setEmail($email);
            $manager->setCreatedAt(new \DateTime());

            $roles = $role === 'ROLE_ADMIN' ? ['ROLE_ADMIN'] : ['ROLE_USER'];
            $manager->setRoles($roles);

            $manager->setPassword($hasher->hashPassword($manager, $password));

            $manager->setCompany($companyId ? $companyRepo->find($companyId) : null);

            $em->persist($manager);
            $em->flush();

            $this->addFlash('success', 'Manager added successfully.');
            return $this->redirectToRoute('app_admin_managers');
        }

        return $this->render('admin/managers/form.html.twig', [
            'manager'   => $manager,
            'companies' => $companyRepo->findAll(),
            'mode'      => 'add',
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_managers_edit')]
    public function edit(
        Manager $manager,
        Request $request,
        EntityManagerInterface $em,
        CompanyRepository $companyRepo,
        UserPasswordHasherInterface $hasher
    ): Response {
        if ($request->isMethod('POST')) {
            $this->clearValidationErrors();

            $firstName = trim($request->request->get('first_name') ?? '');
            $lastName = trim($request->request->get('last_name') ?? '');
            $email = trim($request->request->get('email') ?? '');
            $password = $request->request->get('password');
            $companyId = $request->request->get('company_id');
            $role = $request->request->get('role');

            // Validate required fields
            $this->validateName($firstName, 'First name', true);
            $this->validateName($lastName, 'Last name', true);
            $this->validateEmail($email, 'Email', true);

            // Check if email already exists (excluding current manager)
            $existingManager = $em->getRepository(Manager::class)->findOneBy(['email' => $email]);
            if ($existingManager && $existingManager->getId() !== $manager->getId()) {
                $this->validationErrors[] = 'This email address is already in use.';
            }

            // Validate password if provided
            if ($password) {
                $this->validatePassword($password, false);
            }

            if ($this->hasValidationErrors()) {
                return $this->render('admin/managers/form.html.twig', [
                    'manager'   => $manager,
                    'companies' => $companyRepo->findAll(),
                    'mode'      => 'edit',
                    'error'     => implode(' ', $this->getValidationErrors()),
                ]);
            }

            $manager->setFirstName($firstName);
            $manager->setLastName($lastName);
            $manager->setEmail($email);

            $roles = $role === 'ROLE_ADMIN' ? ['ROLE_ADMIN'] : ['ROLE_USER'];
            $manager->setRoles($roles);

            $manager->setCompany($companyId ? $companyRepo->find($companyId) : null);

            // Only update password if a new one was provided
            if ($password) {
                $manager->setPassword($hasher->hashPassword($manager, $password));
            }

            $em->flush();

            $this->addFlash('success', 'Manager updated successfully.');
            return $this->redirectToRoute('app_admin_managers');
        }

        return $this->render('admin/managers/form.html.twig', [
            'manager'   => $manager,
            'companies' => $companyRepo->findAll(),
            'mode'      => 'edit',
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_managers_delete', methods: ['POST'])]
    public function delete(Manager $manager, EntityManagerInterface $em): Response
    {
        $em->remove($manager);
        $em->flush();

        $this->addFlash('success', 'Manager deleted.');
        return $this->redirectToRoute('app_admin_managers');
    }
}