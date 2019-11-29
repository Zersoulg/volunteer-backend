<?php

namespace App\EmailBundle\Controller;

use App\EmailBundle\Entity\Email;
use App\EmailBundle\Services\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


class SendEmail
{

    public $emailService;
    public $em;

    public function __construct(SendMailService $emailService, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->emailService = $emailService;
    }

    public function __invoke(Email $data)
    {
        $email = $data['to'];
        $textMail =$data['message'];
        $topic = $data['topic'];

        $this->emailService->createMail($email, $topic, $textMail);
        return new JsonResponse(['ms' => 'ok']);
    }

}
