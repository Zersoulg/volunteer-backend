<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 24.12.18
 * Time: 12:45
 */

namespace App\UserBundle\Controller;


use App\UserBundle\Entity\User;
use App\EmailBundle\Services\SendMailService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SendMailForActivation
{
    private $mailer;
    private $url;

    public function __construct(SendMailService $mailer, ContainerInterface $container)
    {
        $this->mailer = $mailer;
        $this->url = $container->getParameter('site_url');
    }

    public function __invoke(User $data)
    {
        if (!$data->isEnabled()) {

            $rand = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', ceil(10 / strlen($x)))), 1, 10);
            $id = $data->getId();
            $body = "activation?u=$id&code=$rand";

            $this->mailer->createMail($data->getEmail(), 'Активация аккаунта', "Ссылка для активации аккаунта: $this->url$body");
            return JsonResponse::HTTP_CREATED;
        }

        throw new HttpException(400, 'This account is already activate!');
    }
}
