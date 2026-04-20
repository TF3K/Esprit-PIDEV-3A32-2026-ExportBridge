<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

require __DIR__ . '/vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$dsn = $_ENV['MAILER_DSN'] ?? 'null://null';
echo "Using DSN: $dsn\n";

try {
    $transport = Transport::fromDsn($dsn);
    $mailer = new Mailer($transport);

    $email = (new Email())
        ->from('yasminehmila2@gmail.com')
        ->to('yasminehmila2@gmail.com')
        ->subject('Test Mailer DSN')
        ->text('If you see this, the mailer is working.');

    $mailer->send($email);
    echo "Email sent successfully.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
