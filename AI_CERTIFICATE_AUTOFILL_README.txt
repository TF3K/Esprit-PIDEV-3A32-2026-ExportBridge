AI Certificate Auto-Fill - Setup

What was added:
1. A new AI extraction service:
   src/Service/AiCertificateExtractorService.php

2. A new AJAX route in:
   src/Controller/User/UserCertificateController.php

   Route:
   POST /dashboard/certificates/ai-extract

3. An updated certificate form:
   templates/user/certificates/form.html.twig

How to use it:
1. Go to Dashboard > Certificates > Add Certificate or Edit Certificate.
2. Choose a certificate PDF in the Certificate PDF file input.
3. Click "Extract with AI".
4. The form fills certificate number, type, issuing authority, country, issue date, and expiry date.
5. Review the fields manually, then save the certificate.

No-card / presentation setup:
- This version includes DEMO MODE so you can present the modern AI workflow without buying API credits.
- Create a .env.local file in the project root and add:

  AI_CERTIFICATE_DEMO_MODE=1
  OPENAI_API_KEY=

- In demo mode, the button works and fills realistic sample data. The UI shows a "Demo mode" badge.

Real OpenAI setup later:
1. Add billing credits on OpenAI Platform.
2. Put your real key in .env.local:

   OPENAI_API_KEY=your-real-key-here
   AI_CERTIFICATE_DEMO_MODE=0

3. Keep .env.local private and do not upload it to GitHub.

Notes:
- Real mode uses the OpenAI Responses API with PDF input and structured JSON output.
- Demo mode avoids quota/billing errors during soutenance.
- PDFs should be smaller than 50 MB in real mode.
