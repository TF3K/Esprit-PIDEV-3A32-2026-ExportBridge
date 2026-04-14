<?php

namespace App\Controller\Admin;

use App\Entity\ProductCategory;
use App\Repository\ProductCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Traits\FormValidationTrait;

#[Route('/admin/product-categories')]
class ProductCategoryController extends AbstractController
{
    use FormValidationTrait;

    #[Route('', name: 'app_admin_product_categories')]
    public function index(ProductCategoryRepository $repo): Response
    {
        return $this->render('admin/product_categories/index.html.twig', [
            'categories' => $repo->findAll(),
        ]);
    }

    #[Route('/add', name: 'app_admin_product_categories_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $category = new ProductCategory();
        $errors   = [];
        $old      = [];

        if ($request->isMethod('POST')) {
            $name        = trim($request->request->get('name') ?? '');
            $slug        = trim($request->request->get('slug') ?? '');
            $description = trim($request->request->get('description') ?? '');

            $old = compact('name', 'slug', 'description');

            $this->clearValidationErrors();

            $this->validateRequired($name, 'Name', 2, 100);
            if ($this->hasValidationErrors()) {
                $errors['name'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateAlphanumeric($slug, 'Slug', true);
            if ($this->hasValidationErrors()) {
                $errors['slug'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateRequired($description, 'Description', 10);
            if ($this->hasValidationErrors()) {
                $errors['description'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            if (empty($errors)) {
                $category->setName($name);
                $category->setSlug($slug);
                $category->setDescription($description);

                $em->persist($category);
                $em->flush();

                $this->addFlash('success', 'Category added successfully.');
                return $this->redirectToRoute('app_admin_product_categories');
            }
        }

        return $this->render('admin/product_categories/form.html.twig', [
            'category' => $category,
            'mode'     => 'add',
            'errors'   => $errors,
            'old'      => $old,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_product_categories_edit')]
    public function edit(ProductCategory $category, Request $request, EntityManagerInterface $em): Response
    {
        $errors = [];
        $old    = [];
        

        if ($request->isMethod('POST')) {
            $name        = trim($request->request->get('name') ?? '');
            $slug        = trim($request->request->get('slug') ?? '');
            $description = trim($request->request->get('description') ?? '');

            $old = compact('name', 'slug', 'description');

            $this->clearValidationErrors();

            $this->validateName($name, 'Name', true);
            if ($this->hasValidationErrors()) {
                $errors['name'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateAlphanumeric($slug, 'Slug', true);
            if ($this->hasValidationErrors()) {
                $errors['slug'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateRequired($description, 'Description', 10);
            if ($this->hasValidationErrors()) {
                $errors['description'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            if (empty($errors)) {
                $category->setName($name);
                $category->setSlug($slug);
                $category->setDescription($description);

                $em->flush();

                $this->addFlash('success', 'Category updated successfully.');
                return $this->redirectToRoute('app_admin_product_categories');
            }
        }

        return $this->render('admin/product_categories/form.html.twig', [
            'category' => $category,
            'mode'     => 'edit',
            'errors'   => $errors,
            'old'      => $old,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_product_categories_delete', methods: ['POST'])]
    public function delete(ProductCategory $category, EntityManagerInterface $em): Response
    {
        $em->remove($category);
        $em->flush();

        $this->addFlash('success', 'Category deleted.');
        return $this->redirectToRoute('app_admin_product_categories');
    }
}