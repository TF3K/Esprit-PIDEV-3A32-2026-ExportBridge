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
use App\Traits\FormValidationTrait;

#[Route('/admin/partnerships')]
class PartnershipController extends AbstractController
{
    use FormValidationTrait;

    private const TYPES    = ['Export', 'Import', 'Distribution', 'Joint Venture', 'Strategic Alliance'];
    private const STATUSES = ['active', 'pending', 'terminated', 'suspended'];

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
        $errors      = [];
        $old         = [];

        if ($request->isMethod('POST')) {
            $companyId       = $request->request->get('company_id');
            $type            = trim($request->request->get('type') ?? '');
            $status          = trim($request->request->get('status') ?? '');
            $establishedDate = $request->request->get('established_date');
            $terminatedDate  = $request->request->get('terminated_date');
            $notes           = trim($request->request->get('notes') ?? '');

            $old = compact('companyId', 'type', 'status', 'establishedDate', 'terminatedDate', 'notes');

            $this->clearValidationErrors();

            $this->validateRequired($companyId, 'Company', 1);
            if ($this->hasValidationErrors()) {
                $errors['company_id'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateSelection($type, self::TYPES, 'Type', false);
            if ($this->hasValidationErrors()) {
                $errors['type'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateSelection($status, self::STATUSES, 'Status', true);
            if ($this->hasValidationErrors()) {
                $errors['status'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateRequired($notes, 'Notes', 10);
            if ($this->hasValidationErrors()) {
                $errors['notes'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateDate($establishedDate, 'Established date', false);
            if ($this->hasValidationErrors()) {
                $errors['established_date'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateDate($terminatedDate, 'Terminated date', false);
            if ($this->hasValidationErrors()) {
                $errors['terminated_date'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            if (!isset($errors['established_date']) && !isset($errors['terminated_date'])
                && $establishedDate && $terminatedDate) {
                $this->validateDateRange($establishedDate, $terminatedDate);
                if ($this->hasValidationErrors()) {
                    $errors['terminated_date'] = $this->getFirstValidationError();
                    $this->clearValidationErrors();
                }
            }

            if (empty($errors)) {
                $this->applyToEntity($partnership, $type, $status, $notes,
                    $establishedDate, $terminatedDate, $companyId, $companyRepo);
                $partnership->setCreatedAt(new \DateTime());
                $partnership->setLastUpdated(new \DateTime());

                $em->persist($partnership);
                $em->flush();

                $this->addFlash('success', 'Partnership added successfully.');
                return $this->redirectToRoute('app_admin_partnerships');
            }
        }

        return $this->render('admin/partnerships/form.html.twig', [
            'partnership' => $partnership,
            'companies'   => $companyRepo->findAll(),
            'types'       => self::TYPES,
            'statuses'    => self::STATUSES,
            'mode'        => 'add',
            'errors'      => $errors,
            'old'         => $old,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_partnerships_edit')]
    public function edit(
        Partnership $partnership,
        Request $request,
        EntityManagerInterface $em,
        CompanyRepository $companyRepo
    ): Response {
        $errors = [];
        $old    = [];

        if ($request->isMethod('POST')) {
            $companyId       = $request->request->get('company_id');
            $type            = trim($request->request->get('type') ?? '');
            $status          = trim($request->request->get('status') ?? '');
            $establishedDate = $request->request->get('established_date');
            $terminatedDate  = $request->request->get('terminated_date');
            $notes           = trim($request->request->get('notes') ?? '');

            $old = compact('companyId', 'type', 'status', 'establishedDate', 'terminatedDate', 'notes');

            $this->clearValidationErrors();

            $this->validateRequired($companyId, 'Company', 1);
            if ($this->hasValidationErrors()) {
                $errors['company_id'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateSelection($type, self::TYPES, 'Type', false);
            if ($this->hasValidationErrors()) {
                $errors['type'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateSelection($status, self::STATUSES, 'Status', true);
            if ($this->hasValidationErrors()) {
                $errors['status'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateRequired($notes, 'Notes', 10);
            if ($this->hasValidationErrors()) {
                $errors['notes'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateDate($establishedDate, 'Established date', false);
            if ($this->hasValidationErrors()) {
                $errors['established_date'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateDate($terminatedDate, 'Terminated date', false);
            if ($this->hasValidationErrors()) {
                $errors['terminated_date'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            if (!isset($errors['established_date']) && !isset($errors['terminated_date'])
                && $establishedDate && $terminatedDate) {
                $this->validateDateRange($establishedDate, $terminatedDate);
                if ($this->hasValidationErrors()) {
                    $errors['terminated_date'] = $this->getFirstValidationError();
                    $this->clearValidationErrors();
                }
            }

            if (empty($errors)) {
                $this->applyToEntity($partnership, $type, $status, $notes,
                    $establishedDate, $terminatedDate, $companyId, $companyRepo);
                $partnership->setLastUpdated(new \DateTime());

                $em->flush();

                $this->addFlash('success', 'Partnership updated successfully.');
                return $this->redirectToRoute('app_admin_partnerships');
            }
        }

        return $this->render('admin/partnerships/form.html.twig', [
            'partnership' => $partnership,
            'companies'   => $companyRepo->findAll(),
            'types'       => self::TYPES,
            'statuses'    => self::STATUSES,
            'mode'        => 'edit',
            'errors'      => $errors,
            'old'         => $old,
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
        $collaboration->setTitle($request->request->get('title'));
        $collaboration->setDescription($request->request->get('description'));
        $collaboration->setStatus($request->request->get('status'));
        $collaboration->setCreatedAt(new \DateTime());
        $collaboration->setLastUpdated(new \DateTime());

        $startDate = $request->request->get('start_date');
        $collaboration->setStartDate($startDate ? new \DateTime($startDate) : null);

        $endDate = $request->request->get('end_date');
        $collaboration->setEndDate($endDate ? new \DateTime($endDate) : null);

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

        if ($collaboration && $collaboration->getPartnership()->getId() === $partnership->getId()) {
            $em->remove($collaboration);
            $em->flush();
            $this->addFlash('success', 'Collaboration removed.');
        }

        return $this->redirectToRoute('app_admin_partnerships_show', ['id' => $partnership->getId()]);
    }

    private function applyToEntity(
        Partnership $partnership,
        string $type,
        string $status,
        string $notes,
        ?string $establishedDate,
        ?string $terminatedDate,
        mixed $companyId,
        CompanyRepository $companyRepo
    ): void {
        $partnership->setType($type);
        $partnership->setStatus($status);
        $partnership->setNotes($notes);
        $partnership->setEstablishedDate($establishedDate ? new \DateTime($establishedDate) : null);
        $partnership->setTerminatedDate($terminatedDate ? new \DateTime($terminatedDate) : null);
        $partnership->setCompany($companyId ? $companyRepo->find($companyId) : null);
    }
}