<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\CompanyRepository;
use App\Repository\ProductCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Traits\FormValidationTrait;

#[Route('/admin/products')]
class ProductController extends AbstractController
{
    use FormValidationTrait;

    #[Route('', name: 'app_admin_products')]
    public function index(ProductRepository $repo): Response
    {
        return $this->render('admin/products/index.html.twig', [
            'products' => $repo->findAll(),
        ]);
    }

    #[Route('/add', name: 'app_admin_products_add')]
    public function add(
        Request $request,
        EntityManagerInterface $em,
        CompanyRepository $companyRepo,
        ProductCategoryRepository $categoryRepo
    ): Response {
        $product = new Product();
        $errors  = [];
        $old     = [];

        if ($request->isMethod('POST')) {
            $name           = trim($request->request->get('name') ?? '');
            $hsCode         = trim($request->request->get('hs_code') ?? '');
            $description    = trim($request->request->get('description') ?? '');
            $quantity       = $request->request->get('quantity');
            $unit           = trim($request->request->get('unit') ?? '');
            $unitPrice      = $request->request->get('unit_price');
            $currency       = trim($request->request->get('currency') ?? '');
            $originCriteria = trim($request->request->get('origin_criteria') ?? '');
            $companyId      = $request->request->get('company_id');
            $categoryId     = $request->request->get('category_id');

            $old = compact(
                'name', 'hsCode', 'description', 'quantity', 'unit',
                'unitPrice', 'currency', 'originCriteria', 'companyId', 'categoryId'
            );

            $this->clearValidationErrors();

            $this->validateName($name, 'Product name', true);
            if ($this->hasValidationErrors()) {
                $errors['name'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateHsCode($hsCode, false);
            if ($this->hasValidationErrors()) {
                $errors['hs_code'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateRequired($description, 'Description', 1);
            if ($this->hasValidationErrors()) {
                $errors['description'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateRequired($currency, 'Currency', 1);
            if ($this->hasValidationErrors()) {
                $errors['currency'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            if ($quantity !== null && $quantity !== '') {
                $this->validateNumber($quantity, 'Quantity', false, 0);
                if ($this->hasValidationErrors()) {
                    $errors['quantity'] = $this->getFirstValidationError();
                    $this->clearValidationErrors();
                }
            }

            if ($unitPrice !== null && $unitPrice !== '') {
                $this->validateNumber($unitPrice, 'Unit price', false, 0);
                if ($this->hasValidationErrors()) {
                    $errors['unit_price'] = $this->getFirstValidationError();
                    $this->clearValidationErrors();
                }
            }

            $this->validateName($originCriteria, 'Origin criteria', false);
            if ($this->hasValidationErrors()) {
                $errors['origin_criteria'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            if (empty($errors)) {
                $this->applyToEntity($product, $name, $hsCode, $description, $quantity, $unit,
                    $unitPrice, $currency, $originCriteria, $companyId, $categoryId,
                    $companyRepo, $categoryRepo);
                $product->setCreatedAt(new \DateTime());
                $product->setLastUpdated(new \DateTime());

                $em->persist($product);
                $em->flush();

                $this->addFlash('success', 'Product added successfully.');
                return $this->redirectToRoute('app_admin_products');
            }
        }

        return $this->render('admin/products/form.html.twig', [
            'product'    => $product,
            'companies'  => $companyRepo->findAll(),
            'categories' => $categoryRepo->findAll(),
            'mode'       => 'add',
            'errors'     => $errors,
            'old'        => $old,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_products_edit')]
    public function edit(
        Product $product,
        Request $request,
        EntityManagerInterface $em,
        CompanyRepository $companyRepo,
        ProductCategoryRepository $categoryRepo
    ): Response {
        $errors = [];
        $old    = [];

        if ($request->isMethod('POST')) {
            $name           = trim($request->request->get('name') ?? '');
            $hsCode         = trim($request->request->get('hs_code') ?? '');
            $description    = trim($request->request->get('description') ?? '');
            $quantity       = $request->request->get('quantity');
            $unit           = trim($request->request->get('unit') ?? '');
            $unitPrice      = $request->request->get('unit_price');
            $currency       = trim($request->request->get('currency') ?? '');
            $originCriteria = trim($request->request->get('origin_criteria') ?? '');
            $companyId      = $request->request->get('company_id');
            $categoryId     = $request->request->get('category_id');

            $old = compact(
                'name', 'hsCode', 'description', 'quantity', 'unit',
                'unitPrice', 'currency', 'originCriteria', 'companyId', 'categoryId'
            );

            $this->clearValidationErrors();

            $this->validateName($name, 'Product name', true);
            if ($this->hasValidationErrors()) {
                $errors['name'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateHsCode($hsCode, false);
            if ($this->hasValidationErrors()) {
                $errors['hs_code'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateRequired($description, 'Description', 1);
            if ($this->hasValidationErrors()) {
                $errors['description'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateRequired($currency, 'Currency', 1);
            if ($this->hasValidationErrors()) {
                $errors['currency'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            if ($quantity !== null && $quantity !== '') {
                $this->validateNumber($quantity, 'Quantity', false, 0);
                if ($this->hasValidationErrors()) {
                    $errors['quantity'] = $this->getFirstValidationError();
                    $this->clearValidationErrors();
                }
            }

            if ($unitPrice !== null && $unitPrice !== '') {
                $this->validateNumber($unitPrice, 'Unit price', false, 0);
                if ($this->hasValidationErrors()) {
                    $errors['unit_price'] = $this->getFirstValidationError();
                    $this->clearValidationErrors();
                }
            }

            $this->validateName($originCriteria, 'Origin criteria', false);
            if ($this->hasValidationErrors()) {
                $errors['origin_criteria'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            if (empty($errors)) {
                $this->applyToEntity($product, $name, $hsCode, $description, $quantity, $unit,
                    $unitPrice, $currency, $originCriteria, $companyId, $categoryId,
                    $companyRepo, $categoryRepo);
                $product->setLastUpdated(new \DateTime());

                $em->flush();

                $this->addFlash('success', 'Product updated successfully.');
                return $this->redirectToRoute('app_admin_products');
            }
        }

        return $this->render('admin/products/form.html.twig', [
            'product'    => $product,
            'companies'  => $companyRepo->findAll(),
            'categories' => $categoryRepo->findAll(),
            'mode'       => 'edit',
            'errors'     => $errors,
            'old'        => $old,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_products_delete', methods: ['POST'])]
    public function delete(Product $product, EntityManagerInterface $em): Response
    {
        $em->remove($product);
        $em->flush();

        $this->addFlash('success', 'Product deleted.');
        return $this->redirectToRoute('app_admin_products');
    }

    private function applyToEntity(
        Product $product,
        string $name,
        string $hsCode,
        string $description,
        mixed $quantity,
        string $unit,
        mixed $unitPrice,
        string $currency,
        string $originCriteria,
        mixed $companyId,
        mixed $categoryId,
        CompanyRepository $companyRepo,
        ProductCategoryRepository $categoryRepo
    ): void {
        $product->setName($name);
        $product->setDescription($description);
        $product->setHsCode($hsCode);
        $product->setQuantity($quantity !== null && $quantity !== '' ? (float) $quantity : null);
        $product->setUnit($unit);
        $product->setUnitPrice($unitPrice !== null && $unitPrice !== '' ? (float) $unitPrice : null);
        $product->setCurrency($currency);
        $product->setOriginCriteria($originCriteria);
        $product->setCompany($companyId ? $companyRepo->find($companyId) : null);
        $product->setProductCategory($categoryId ? $categoryRepo->find($categoryId) : null);
    }
}