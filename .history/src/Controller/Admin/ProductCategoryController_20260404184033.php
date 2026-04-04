<?php

namespace App\Controller\Admin;

use App\Entity\ProductCategory;
use App\Repository\ProductCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/product-categories')]
class ProductCategoryController extends AbstractController
{
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

        if ($request->isMethod('POST')) {
            $category->setName($request->request->get('name'));
            $category->setDescription($request->request->get('description'));
            $category->setSlug($request->request->get('slug'));

            $em->persist($category);
            $em->flush();

            $this->addFlash('success', 'Category added successfully.');
            return $this->redirectToRoute('app_admin_product_categories');
        }

        return $this->render('admin/product_categories/form.html.twig', [
            'category' => $category,
            'mode' => 'add',
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_product_categories_edit')]
    public function edit(ProductCategory $category, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $category->setName($request->request->get('name'));
            $category->setDescription($request->request->get('description'));
            $category->setSlug($request->request->get('slug'));

            $em->flush();

            $this->addFlash('success', 'Category updated successfully.');
            return $this->redirectToRoute('app_admin_product_categories');
        }

        return $this->render('admin/product_categories/form.html.twig', [
            'category' => $category,
            'mode' => 'edit',
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_product_categories_delete', methods: ['POST'])]
    public function delete(ProductCategorie $category, EntityManagerInterface $em): Response
    {
        $em->remove($category);
        $em->flush();

        $this->addFlash('success', 'Category deleted.');
        return $this->redirectToRoute('app_admin_product_categories');
    }
}