<?php

namespace App\Service;

use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;


class SendMailService
{

    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function send(
        string $from,
        string $to,
        string $subject,
        string $template,
        array $context
    ): void
    
    {
        // Création du mail
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate("emails/$template.html.twig")
            ->context($context);

        // Envoi du mail
        $this->mailer->send($email);
    }

    public function sendAdminNotification(
        string $from,
        string $adminEmail,
        string $subject,
        string $template,
        array $context
    ): void {
        
        // Création du mail pour l'administrateur
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($adminEmail)
            ->subject($subject)
            ->htmlTemplate("emails/$template.html.twig")
            ->context($context);

        // Envoi du mail à l'administrateur
        $this->mailer->send($email);
    }

}