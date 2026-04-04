<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\CompanyRepository;
use App\Repository\ProductCategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/products')]
class ProductController extends AbstractController
{
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
        CompanyRepository $CompanyRepo,
        ProductCategorieRepository $categoryRepo
    ): Response {
        $product = new Product();

        if ($request->isMethod('POST')) {
            $this->handleForm($product, $request, $CompanyRepo, $categoryRepo);
            $product->setCreatedAt(new \DateTime());
            $product->setLastUpdated(new \DateTime());

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Product added successfully.');
            return $this->redirectToRoute('app_admin_products');
        }

        return $this->render('admin/products/form.html.twig', [
            'product'    => $product,
            'Companys'  => $CompanyRepo->findAll(),
            'categories' => $categoryRepo->findAll(),
            'mode'       => 'add',
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_products_edit')]
    public function edit(
        Product $product,
        Request $request,
        EntityManagerInterface $em,
        CompanyRepository $CompanyRepo,
        ProductCategorieRepository $categoryRepo
    ): Response {
        if ($request->isMethod('POST')) {
            $this->handleForm($product, $request, $CompanyRepo, $categoryRepo);
            $product->setLastUpdated(new \DateTime());

            $em->flush();

            $this->addFlash('success', 'Product updated successfully.');
            return $this->redirectToRoute('app_admin_products');
        }

        return $this->render('admin/products/form.html.twig', [
            'product'    => $product,
            'Companys'  => $CompanyRepo->findAll(),
            'categories' => $categoryRepo->findAll(),
            'mode'       => 'edit',
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

    private function handleForm(
        Product $product,
        Request $request,
        CompanyRepository $companyRepo,
        ProductCategoryRepository $categoryRepo
    ): void {
        $product->setName($request->request->get('name'));
        $product->setDescription($request->request->get('description'));
        $product->setHsCode($request->request->get('hs_code'));
        $product->setQuantity($request->request->get('quantity') ? (float) $request->request->get('quantity') : null);
        $product->setUnit($request->request->get('unit'));
        $product->setUnitPrice($request->request->get('unit_price') ? (float) $request->request->get('unit_price') : null);
        $product->setCurrency($request->request->get('currency'));
        $product->setOriginCriteria($request->request->get('origin_criteria'));

        $companyId = $request->request->get('company_id');
        $product->setCompany($companyId ? $companyRepo->find($companyId) : null);

        $categoryId = $request->request->get('category_id');
        $product->setProductCategory($categoryId ? $categoryRepo->find($categoryId) : null);
    }
}