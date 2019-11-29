<?php

namespace App\EmailBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;


class SendMailService
{
    private $mailer;
    private $senderEmail;

    public function __construct(\Swift_Mailer $mailer, ContainerInterface $container)
    {
        $this->mailer = $mailer;
        $this->senderEmail = $container->getParameter('sender_email');
    }

    public function createMail($recipientsMail,$topic, $text): void
    {
        $message = (new \Swift_Message($topic))
            ->setFrom($this->senderEmail)
            ->setTo($recipientsMail)
            ->setBody($text);
        $this->mailer->send($message);

    }

}
