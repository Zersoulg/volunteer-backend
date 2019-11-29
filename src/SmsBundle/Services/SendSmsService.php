<?php

namespace App\SmsBundle\Services;

use App\SmsBundle\Entity\Sms;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SendSmsService
{

    private $key;
    private $is_test_mode;
    private $em;

    public function __construct(ContainerInterface $container,EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->key = $container->getParameter('sms_api_key');
        $this->is_test_mode = $container->getParameter('sms_is_test');
    }

    public function sendMessage($recipient, $message,$user = null): void
    {

        $url = "http://sms.ru/sms/send?";
        $url .= http_build_query([
            "api_id" => $this->key,
            "to" => $recipient,
            "msg" => urlencode($message),
            "json" => 1,
        ]);
        if ($this->is_test_mode) {
            $url .= "&test=1";
        }
        $data = json_decode($this->makeRequest($url),true);

        $sms = new Sms();
        $sms->phone = $recipient;
        $sms->message = $message;
        $sms->status = $data['status_code'];
        if (isset($user)){
            $sms->user = $user;
        }
        $this->em->persist($sms);
        $this->em->flush();
    }

    /**
     * Executes request on link.
     * @param   string $url
     * @param   string $method
     * @param   array $postfields
     * @return  string
     */
    private function makeRequest($url, $method = 'GET', $postfields = []): string
    {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POST => ($method == 'POST'),
            CURLOPT_POSTFIELDS => $postfields,
            CURLOPT_URL => $url
        ));
        return curl_exec($ch);
    }
}