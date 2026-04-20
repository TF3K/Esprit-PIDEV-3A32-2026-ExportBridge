<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\CompanyRepository;
use App\Repository\ProductCategoryRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Doctrine\ORM\EntityManagerInterface;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Traits\FormValidationTrait;

#[Route('/admin/products')]
class ProductController extends AbstractController
{
    use FormValidationTrait;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%env(string:GROQ_API_KEY)%')]
        private readonly string $groqApiKey = ''
    ) {
    }

    #[Route('/mail', name: 'app_admin_products_mail', methods: ['GET', 'POST'])]
    public function mail(Request $request, ProductRepository $repo, MailerInterface $mailer): Response
    {
        $products = $repo->findAll();

        if ($request->isMethod('POST')) {
            $token = $request->request->get('_token');
            if (!$this->isCsrfTokenValid('send-mail', (string) $token)) {
                $this->addFlash('danger', 'Invalid CSRF token.');
                return $this->redirectToRoute('app_admin_products_mail');
            }

            $recipientEmail = trim((string) $request->request->get('recipient_email'));
            $subject = trim((string) $request->request->get('subject'));
            $productIds = $request->request->all('product_ids');

            if (empty($recipientEmail) || empty($productIds)) {
                $this->addFlash('danger', 'Please provide a recipient email and select at least one product.');
                return $this->redirectToRoute('app_admin_products_mail');
            }

            $selectedProducts = $repo->findBy(['id' => $productIds]);

            if (count($selectedProducts) === 0) {
                $this->addFlash('danger', 'No valid products selected.');
                return $this->redirectToRoute('app_admin_products_mail');
            }

            $email = (new TemplatedEmail())
                ->from('yasminehmila2@gmail.com')
                ->to($recipientEmail)
                ->subject($subject ?: 'Available Products Information')
                ->htmlTemplate('emails/product_info.html.twig')
                ->context([
                    'products' => $selectedProducts,
                    'subject' => $subject ?: 'Available Products Information',
                ]);

            try {
                $mailer->send($email);
                $this->addFlash('success', 'Email sent successfully.');
                return $this->redirectToRoute('app_admin_products');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Error sending email: ' . $e->getMessage());
            }
        }

        return $this->render('admin/products/mail.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('', name: 'app_admin_products')]
    public function index(ProductRepository $repo): Response
    {
        $products = $repo->findAll();
        $barcodeDataUris = [];

        foreach ($products as $product) {
            $barcodeDataUris[$product->getId()] = $this->buildBarcodeDataUri($product);
        }

        return $this->render('admin/products/index.html.twig', [
            'products' => $products,
            'barcodeDataUris' => $barcodeDataUris,
        ]);
    }

    #[Route('/show/{id}', name: 'app_admin_products_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('admin/products/show.html.twig', [
            'product' => $product,
            'barcodeDataUri' => $this->buildBarcodeDataUri($product),
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
            $imageFile      = $request->files->get('image');

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

                if ($imageFile instanceof UploadedFile) {
                    try {
                        $product->setImagePath($this->uploadProductImage($imageFile));
                    } catch (\RuntimeException $e) {
                        $errors['image'] = $e->getMessage();
                    }
                }

                if (!empty($errors)) {
                    return $this->render('admin/products/form.html.twig', [
                        'product'    => $product,
                        'companies'  => $companyRepo->findAll(),
                        'categories' => $categoryRepo->findAll(),
                        'mode'       => 'add',
                        'errors'     => $errors,
                        'old'        => $old,
                    ]);
                }

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
            $imageFile      = $request->files->get('image');

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

                if ($imageFile instanceof UploadedFile) {
                    try {
                        $product->setImagePath($this->uploadProductImage($imageFile, $product->getImagePath()));
                    } catch (\RuntimeException $e) {
                        $errors['image'] = $e->getMessage();
                    }
                }

                if (!empty($errors)) {
                    return $this->render('admin/products/form.html.twig', [
                        'product'    => $product,
                        'companies'  => $companyRepo->findAll(),
                        'categories' => $categoryRepo->findAll(),
                        'mode'       => 'edit',
                        'errors'     => $errors,
                        'old'        => $old,
                    ]);
                }

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

    #[Route('/{id}/barcode', name: 'app_admin_products_barcode', methods: ['GET'])]
    public function barcode(Product $product): Response
    {
        $result = $this->buildBarcodeSvg($product);

        return new Response($result, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-store, max-age=0',
        ]);
    }

    #[Route('/ai-description', name: 'app_admin_products_ai_description', methods: ['POST'])]
    public function aiDescription(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return new JsonResponse(['error' => 'Invalid payload.'], 400);
        }

        if ('' === trim($this->groqApiKey)) {
            return new JsonResponse([
                'error' => 'GROQ_API_KEY is missing. Add it to your local environment file.'
            ], 400);
        }

        $draft = [
            'name' => trim((string) ($payload['name'] ?? '')),
            'hs_code' => trim((string) ($payload['hs_code'] ?? '')),
            'quantity' => trim((string) ($payload['quantity'] ?? '')),
            'unit' => trim((string) ($payload['unit'] ?? '')),
            'unit_price' => trim((string) ($payload['unit_price'] ?? '')),
            'currency' => trim((string) ($payload['currency'] ?? '')),
            'origin_criteria' => trim((string) ($payload['origin_criteria'] ?? '')),
            'category' => trim((string) ($payload['category'] ?? '')),
            'company' => trim((string) ($payload['company'] ?? '')),
        ];

        if ('' === $draft['name']) {
            return new JsonResponse(['error' => 'Product name is required for AI drafting.'], 400);
        }

        $prompt = sprintf(
            "Write a concise commercial product description (60-120 words) for export documentation. Keep it factual and avoid marketing hype. Draft data: Name=%s; HS Code=%s; Quantity=%s; Unit=%s; Unit Price=%s; Currency=%s; Origin Criteria=%s; Category=%s; Company=%s",
            $draft['name'],
            $draft['hs_code'] ?: 'N/A',
            $draft['quantity'] ?: 'N/A',
            $draft['unit'] ?: 'N/A',
            $draft['unit_price'] ?: 'N/A',
            $draft['currency'] ?: 'N/A',
            $draft['origin_criteria'] ?: 'N/A',
            $draft['category'] ?: 'N/A',
            $draft['company'] ?: 'N/A'
        );

        try {
            $response = $this->httpClient->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->groqApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'llama-3.1-8b-instant',
                    'temperature' => 0.4,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You draft accurate product descriptions for trade/export forms.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ],
                'timeout' => 20,
            ]);

            $data = $response->toArray(false);
            $content = trim((string) ($data['choices'][0]['message']['content'] ?? ''));

            if ('' === $content) {
                return new JsonResponse(['error' => 'AI service returned an empty description.'], 502);
            }

            return new JsonResponse(['description' => $content]);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Unable to generate description right now.'], 502);
        }
    }

    #[Route('/ai-translate', name: 'app_admin_products_ai_translate', methods: ['POST'])]
    public function aiTranslate(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return new JsonResponse(['error' => 'Invalid payload.'], 400);
        }

        if ('' === trim($this->groqApiKey)) {
            return new JsonResponse([
                'error' => 'GROQ_API_KEY is missing. Add it to your local environment file.'
            ], 400);
        }

        $text = trim((string) ($payload['text'] ?? ''));
        $targetLanguage = trim((string) ($payload['target_language'] ?? 'English'));

        if ('' === $text) {
            return new JsonResponse(['error' => 'Text is required for translation.'], 400);
        }

        $prompt = sprintf(
            "Translate the following product information into %s. Ensure the translation sounds professional, accurate for international trade, and retains the original formatting or paragraph structure if present. Return ONLY the translated text, no conversational fillers.\n\nText to translate:\n%s",
            $targetLanguage,
            $text
        );

        try {
            $response = $this->httpClient->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->groqApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'llama-3.1-8b-instant',
                    'temperature' => 0.2,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an expert professional translator specializing in commercial product descriptions.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ],
                'timeout' => 20,
            ]);

            $data = $response->toArray(false);
            $content = trim((string) ($data['choices'][0]['message']['content'] ?? ''));

            if ('' === $content) {
                return new JsonResponse(['error' => 'AI service returned an empty translation.'], 502);
            }

            return new JsonResponse(['translation' => $content]);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Unable to translate right now.'], 502);
        }
    }

    #[Route('/export/csv', name: 'app_admin_products_export_csv', methods: ['GET'])]
    public function exportCsv(ProductRepository $repo): StreamedResponse
    {
        $products = $repo->findAll();
        $response = new StreamedResponse(function () use ($products): void {
            $output = fopen('php://output', 'w');
            if (false === $output) {
                return;
            }

            fputcsv($output, ['ID', 'Name', 'Company', 'Category', 'HS Code', 'Quantity', 'Unit', 'Unit Price', 'Currency', 'Origin Criteria', 'Description']);

            foreach ($products as $product) {
                fputcsv($output, [
                    $product->getId(),
                    $product->getName(),
                    $product->getCompany()?->getCompanyName() ?? '',
                    $product->getProductCategory()?->getName() ?? '',
                    $product->getHsCode() ?? '',
                    $product->getQuantity(),
                    $product->getUnit() ?? '',
                    $product->getUnitPrice(),
                    $product->getCurrency() ?? '',
                    $product->getOriginCriteria() ?? '',
                    $product->getDescription() ?? '',
                ]);
            }

            fclose($output);
        });

        $filename = sprintf('products_%s.csv', (new \DateTimeImmutable())->format('Ymd_His'));
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    #[Route('/export/pdf', name: 'app_admin_products_export_pdf', methods: ['GET'])]
    public function exportPdf(ProductRepository $repo): Response
    {
        $products = $repo->findAll();
        $html = $this->renderView('admin/products/export_pdf.html.twig', [
            'products' => $products,
            'generatedAt' => new \DateTimeImmutable(),
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = sprintf('products_%s.pdf', (new \DateTimeImmutable())->format('Ymd_His'));

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
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

    private function uploadProductImage(UploadedFile $file, ?string $oldPath = null): string
    {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes, true)) {
            throw new \RuntimeException('Only JPG, PNG, and WebP images are allowed.');
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/products';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $extension = $file->guessExtension() ?: 'bin';
        $filename = sprintf('product_%s.%s', bin2hex(random_bytes(8)), $extension);
        $file->move($uploadDir, $filename);

        if ($oldPath) {
            $oldAbsolutePath = $this->getParameter('kernel.project_dir') . '/public/' . ltrim($oldPath, '/');
            if (is_file($oldAbsolutePath)) {
                @unlink($oldAbsolutePath);
            }
        }

        return 'uploads/products/' . $filename;
    }

    private function buildBarcodeSvg(Product $product): string
    {
        $scanUrl = $this->generateUrl('app_product_public_show', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $generator = new BarcodeGeneratorSVG();
        return $generator->getBarcode($scanUrl, $generator::TYPE_CODE_128, 2, 60);
    }

    private function buildBarcodeDataUri(Product $product): string
    {
        $svg = $this->buildBarcodeSvg($product);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}