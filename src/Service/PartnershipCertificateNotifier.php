<?php

namespace App\Service;

use App\Entity\Company;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class PartnershipCertificateNotifier
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MailerInterface $mailer,
        private readonly string $defaultSenderEmail,
        private readonly string $notificationRecipientEmail = '',
    ) {
    }

    /**
     * Creates the dashboard notification and, when requested, sends a real email.
     *
     * @return string[] The email addresses used as recipients. Empty when sendEmail is false.
     */
    public function notifyCompany(Company $company, string $type, string $subject, string $message, bool $sendEmail = true): array
    {
        $manager = $company->getManager();

        if ($manager) {
            $notification = new Notification();
            $notification->setManager($manager);
            $notification->setType($type);
            $notification->setMessage($message);
            $notification->setIsRead(false);
            $notification->setCreatedAt(new \DateTime());
            $this->em->persist($notification);
        }

        $recipients = [];
        $this->addRecipient($recipients, $company->getContactEmail());
        $this->addRecipient($recipients, $manager?->getEmail());

        // Local/test recipient. This fixes the admin "Send status email" case where
        // the sender wants a guaranteed copy at the configured Gmail address.
        $forcedRecipient = $this->notificationRecipientEmail ?: ($_ENV['DEFAULT_NOTIFICATION_EMAIL'] ?? $_SERVER['DEFAULT_NOTIFICATION_EMAIL'] ?? $this->defaultSenderEmail);
        $this->addRecipient($recipients, $forcedRecipient);

        if ($sendEmail && count($recipients) > 0) {
            $htmlMessage = '<p>' . nl2br(htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) . '</p>';
            $email = (new Email())
                ->from($this->defaultSenderEmail)
                ->to(...$recipients)
                ->subject($subject)
                ->text($message)
                ->html($htmlMessage);

            $this->mailer->send($email);
        }

        $this->em->flush();

        return $sendEmail ? $recipients : [];
    }

    /** @param string[] $recipients */
    private function addRecipient(array &$recipients, ?string $email): void
    {
        $email = trim((string) $email);
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $lower = strtolower($email);
        foreach ($recipients as $existing) {
            if (strtolower($existing) === $lower) {
                return;
            }
        }

        $recipients[] = $email;
    }
}
