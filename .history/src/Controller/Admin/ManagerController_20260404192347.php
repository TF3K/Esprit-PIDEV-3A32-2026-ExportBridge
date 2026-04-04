<?php

namespace App\Controller\Admin;

use App\Entity\Manager;
use App\Repository\ManagerRepository;
use App\Repository\CompanieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/managers')]
class ManagerController extends AbstractController
{
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
            $manager->setFirstName($request->request->get('first_name'));
            $manager->setLastName($request->request->get('last_name'));
            $manager->setEmail($request->request->get('email'));
            $manager->setCreatedAt(new \DateTime());

            $roles = $request->request->get('role') === 'ROLE_ADMIN'
                ? ['ROLE_ADMIN']
                : ['ROLE_USER'];
            $manager->setRoles($roles);

            $plainPassword = $request->request->get('password');
            $manager->setPassword($hasher->hashPassword($manager, $plainPassword));

            $companyId = $request->request->get('company_id');
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
            $manager->setFirstName($request->request->get('first_name'));
            $manager->setLastName($request->request->get('last_name'));
            $manager->setEmail($request->request->get('email'));

            $roles = $request->request->get('role') === 'ROLE_ADMIN'
                ? ['ROLE_ADMIN']
                : ['ROLE_USER'];
            $manager->setRoles($roles);

            $companyId = $request->request->get('company_id');
            $manager->setCompany($companyId ? $companyRepo->find($companyId) : null);

            // Only update password if a new one was provided
            $plainPassword = $request->request->get('password');
            if ($plainPassword) {
                $manager->setPassword($hasher->hashPassword($manager, $plainPassword));
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