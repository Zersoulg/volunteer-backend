<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 20.12.18
 * Time: 12:36
 */

namespace App\UserBundle\Controller;

use App\CityBundle\Services\CityService;
use App\UserBundle\Entity\User;
use App\EmailBundle\Services\SendMailService;
use App\SmsBundle\Services\SendSmsService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Security;

class CreateUser
{
//    private $mailer;
//    private $url;
//    private $em;
//    private $activation_type;
//    private $smsService;
    private $security, $cityService;

    public function __construct(SendMailService $mailer, ContainerInterface $container, Security $security,
                                EntityManagerInterface $em, SendSmsService $smsService, CityService $cityService)
    {
//        $this->em = $em;
//        $this->mailer = $mailer;
//        $this->smsService = $smsService;
//        $this->url = $container->getParameter('site_url');
//        $this->activation_type = $container->getParameter('account_activation_type');
        $this->security = $security;
        $this->cityService = $cityService;
    }

    /**
     * @param User $data
     * @return User
     * @throws Exception
     */
    public function __invoke(User $data): User
    {
        $data->setPlainPassword($data->getPassword());
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            $data->setRoles([User::ROLE_DEFAULT]);
        } else {
            $data->setRolesRaw($data->getRoles());
        }

        if(isset($data->geoNameId)){
            $data->city = $this->cityService->setCity($data->geoNameId);
        } else {
            throw new HttpException(400, 'Укажите город!');
        }

//        $code = new Code();
//        $code->user = $data;
//        if ($this->activation_type === 'email') {
//
//            $rand = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', ceil(10 / strlen($x)))), 1, 10);
//            $id = $data->getId();
//            $body = "api/users/activate/$id?code=$rand";
//
//            $code->code = $rand;
//            $this->mailer->createMail($data->getEmail(), 'Активация аккаунта', "Ссылка для активации аккаунта: $this->url$body");
//        } else {
//            $code->code = random_int(1000, 9999);
//            $this->smsService->sendMessage($data->phone, $code->code);
//        }
//
//        $this->em->persist($data);
//        $this->em->persist($code);
//        $this->em->flush();

        return $data;
    }
}
