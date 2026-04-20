# ExportBridge - Nouvelles Fonctionnalités 🚀

Ce document récapitule les fonctionnalités récemment implémentées dans le projet **ExportBridge**, incluant la gestion des codes-barres, l'envoi d'e-mails professionnels, et l'intégration de l'intelligence artificielle pour la traduction des produits.

---

## 1. Fonctionnalité : Code-Barres pour les Produits (Scan & Go) 📊

Le système génère désormais automatiquement un **code-barres** unique pour chaque produit, remplaçant l'ancien système de QR Code.

### Technologies & Bundles Utilisés :
- **Bundle / Librairie** : `picqer/php-barcode-generator` (utilisé via Composer pour générer les SVG côté serveur sans dépendance externe).
- **Format** : SVG dynamique converti en Data URI pour l'affichage, format `CODE_128`.

### Comment ça marche ?
- Le contrôleur génère l'URL absolue vers la **page publique** du produit (`/public/products/{id}`).
- Le générateur de code-barres encode cette URL dans un format visuel (Code 128).
- **Utilisation** :
  - Affichage direct sur le profil du produit dans le backoffice.
  - Possibilité de télécharger ou d'ouvrir le SVG en grand format via le bouton "Open Barcode image".
  - Les utilisateurs finaux scannent ce code avec un appareil mobile ou une douchette pour accéder directement aux données du produit.

---

## 2. Fonctionnalité : Envoi d'E-mails (Emailing Multi-Produits) 📧

Un nouveau module d'emailing a été ajouté au tableau de bord pour envoyer les fiches produits directement à des partenaires ou des entreprises clientes.

### Technologies & Bundles Utilisés :
- **Bundle Symfony** : `symfony/mailer` (composant natif de Symfony pour la création et l'envoi d'e-mails).
- **Bundle Twig** : `symfony/twig-bundle` (utilisé via `TemplatedEmail` pour générer le contenu HTML dynamique de l'e-mail).
- **Protocole** : DSN SMTP configuré via la variable d'environnement `MAILER_DSN` dans le `.env`.

### Comment ça marche ?
- **Interface dédiée** : Accessible via `/admin/products/mail`, l'interface permet de sélectionner un ou plusieurs produits depuis la base de données via des cases à cocher.
- **Template d'e-mail** : Un e-mail professionnel et moderne (`emails/product_info.html.twig`) construit dynamiquement. Il affiche une grille des produits sélectionnés (image, nom, description, prix, quantité).
- **Processus** : L'utilisateur saisit l'adresse e-mail de destination, un sujet optionnel, valide le formulaire, et le contrôleur transmet l'e-mail via le `MailerInterface`.

---

## 3. Fonctionnalité : Traduction Instantanée par IA (Llama 3.1) 🌐

Afin de faciliter l'internationalisation des fiches produits sans effort manuel, un module de traduction assistée par Intelligence Artificielle a été intégré à la page de détails des produits.

### Technologies & APIs Utilisées :
- **API Externe** : `Groq API` (endpoint: `https://api.groq.com/openai/v1/chat/completions`).
- **Modèle d'IA** : `llama-3.1-8b-instant` développé par Meta. Choisi pour sa rapidité d'inférence exceptionnelle sur l'infrastructure Groq.
- **Composant Symfony** : `symfony/http-client` (utilisé pour effectuer les requêtes HTTP asynchrones/synchrones vers l'API Groq).
- **Sécurité** : Clé API sécurisée et injectée via la variable d'environnement `%env(string:GROQ_API_KEY)%`.

### Comment ça marche ?
- **Interface UI** : Sur la page de détail d'un produit (`/admin/products/show/{id}`), une section de traduction a été ajoutée.
- **Sélecteur de langues** : L'utilisateur choisit la langue cible (Anglais, Français, Espagnol, Allemand, Arabe, Italien).
- **Traitement** : 
  - Une requête AJAX JS transmet le texte à traduire au contrôleur (`/admin/products/ai-translate`).
  - Le contrôleur (via le `HttpClientInterface`) crée un prompt spécifique et interroge Groq.
  - La réponse est renvoyée en JSON et s'affiche dans un encadré gris sans écraser la description originale du produit.

---

*Document généré pour tracer les évolutions récentes du projet et documenter l'architecture technique employée.*
