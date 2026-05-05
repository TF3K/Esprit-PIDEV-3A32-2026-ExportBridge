<?php

namespace App\Controller\Admin;

use App\Entity\Partnership;
use App\Entity\Collaboration;
use App\Repository\PartnershipRepository;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/partnerships')]
class PartnershipController extends AbstractController
{
    #[Route('', name: 'app_admin_partnerships')]
    public function index(PartnershipRepository $repo): Response
    {
        return $this->render('admin/partnerships/index.html.twig', [
            'partnerships' => $repo->findAll(),
        ]);
    }

    #[Route('/add', name: 'app_admin_partnerships_add')]
    public function add(
        Request $request,
        EntityManagerInterface $em,
        CompanyRepository $companyRepo
    ): Response {
        $partnership = new Partnership();

        if ($request->isMethod('POST')) {
            $this->handleForm($partnership, $request, $companyRepo);
            $partnership->setCreatedAt(new \DateTime());
            $partnership->setLastUpdated(new \DateTime());

            $em->persist($partnership);
            $em->flush();

            $this->addFlash('success', 'Partnership added successfully.');
            return $this->redirectToRoute('app_admin_partnerships');
        }

        return $this->render('admin/partnerships/form.html.twig', [
            'partnership' => $partnership,
            'companies'   => $companyRepo->findAll(),
            'mode'        => 'add',
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_partnerships_edit')]
    public function edit(
        Partnership $partnership,
        Request $request,
        EntityManagerInterface $em,
        CompanyRepository $companyRepo
    ): Response {
        if ($request->isMethod('POST')) {
            $this->handleForm($partnership, $request, $companyRepo);
            $partnership->setLastUpdated(new \DateTime());

            $em->flush();

            $this->addFlash('success', 'Partnership updated successfully.');
            return $this->redirectToRoute('app_admin_partnerships');
        }

        return $this->render('admin/partnerships/form.html.twig', [
            'partnership' => $partnership,
            'companies'   => $companyRepo->findAll(),
            'mode'        => 'edit',
        ]);
    }

    #[Route('/show/{id}', name: 'app_admin_partnerships_show')]
    public function show(Partnership $partnership): Response
    {
        return $this->render('admin/partnerships/show.html.twig', [
            'partnership' => $partnership,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_partnerships_delete', methods: ['POST'])]
    public function delete(Partnership $partnership, EntityManagerInterface $em): Response
    {
        $em->remove($partnership);
        $em->flush();

        $this->addFlash('success', 'Partnership deleted.');
        return $this->redirectToRoute('app_admin_partnerships');
    }

    #[Route('/{id}/collaboration/add', name: 'app_admin_partnerships_collaboration_add', methods: ['POST'])]
    public function addCollaboration(
        Partnership $partnership,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $collaboration = new Collaboration();
        $collaboration->setPartnership($partnership);
        $collaboration->setTitle((string) $request->request->get('title', ''));
        $collaboration->setDescription($request->request->get('description') !== null ? (string) $request->request->get('description') : null);
        $collaboration->setStatus((string) $request->request->get('status', ''));
        $collaboration->setCreatedAt(new \DateTime());
        $collaboration->setLastUpdated(new \DateTime());

        $startDate = $request->request->get('start_date');
        $collaboration->setStartDate($startDate !== null && $startDate !== '' ? new \DateTime((string) $startDate) : null);

        $endDate = $request->request->get('end_date');
        $collaboration->setEndDate($endDate !== null && $endDate !== '' ? new \DateTime((string) $endDate) : null);

        $em->persist($collaboration);
        $em->flush();

        $this->addFlash('success', 'Collaboration added.');
        return $this->redirectToRoute('app_admin_partnerships_show', ['id' => $partnership->getId()]);
    }

    #[Route('/{id}/collaboration/{collaborationId}/delete', name: 'app_admin_partnerships_collaboration_delete', methods: ['POST'])]
    public function deleteCollaboration(
        Partnership $partnership,
        int $collaborationId,
        EntityManagerInterface $em
    ): Response {
        $collaboration = $em->getRepository(Collaboration::class)->find($collaborationId);

        if ($collaboration && ($currentPartnership = $collaboration->getPartnership()) && $currentPartnership->getId() === $partnership->getId()) {
            $em->remove($collaboration);
            $em->flush();
            $this->addFlash('success', 'Collaboration removed.');
        }

        return $this->redirectToRoute('app_admin_partnerships_show', ['id' => $partnership->getId()]);
    }

    private function handleForm(
        Partnership $partnership,
        Request $request,
        CompanyRepository $companyRepo
    ): void {
        $partnership->setStatus((string) $request->request->get('status', ''));
        $partnership->setType($request->request->get('type') !== null ? (string) $request->request->get('type') : null);
        $partnership->setNotes($request->request->get('notes') !== null ? (string) $request->request->get('notes') : null);

        $establishedDate = $request->request->get('established_date');
        $partnership->setEstablishedDate($establishedDate !== null && $establishedDate !== '' ? new \DateTime((string) $establishedDate) : null);

        $terminatedDate = $request->request->get('terminated_date');
        $partnership->setTerminatedDate($terminatedDate !== null && $terminatedDate !== '' ? new \DateTime((string) $terminatedDate) : null);

        $companyId = $request->request->get('company_id');
        $partnership->setCompany($companyId ? $companyRepo->find($companyId) : null);
    }
}
