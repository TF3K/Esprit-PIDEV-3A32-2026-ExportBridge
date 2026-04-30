<?php

namespace App\Controller;

use Picqer\Barcode\BarcodeGeneratorSVG;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductPublicController extends AbstractController
{
    #[Route('/product/{id}/info', name: 'app_product_public_show', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function show(int $id, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Product not found or no longer available.');
        }

        $publicUrl = $this->generateUrl('app_product_public_show', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $generator = new BarcodeGeneratorSVG();
        $svg = $generator->getBarcode($publicUrl, $generator::TYPE_CODE_128, 2, 60);
        $barcodeDataUri = 'data:image/svg+xml;base64,' . base64_encode($svg);

        return $this->render('product/public_show.html.twig', [
            'product' => $product,
            'publicUrl' => $publicUrl,
            'barcodeDataUri' => $barcodeDataUri,
        ]);
    }
}
