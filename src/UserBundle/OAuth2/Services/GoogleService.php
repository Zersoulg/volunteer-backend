<?php

namespace App\UserBundle\OAuth2\Services;

use App\UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Doctrine\UserManager;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class GoogleService
{
    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var Container
     */
    private $container;

    /**
     * @var mixed
     */
    private $googleId;

    /**
     * @var string
     */
    private $googleSecret;

    /**
     * @var mixed
     */
    private $googleApiKey;

    /**
     * @var mixed
     */
    private $redirectURI;

    private $em;

    public function __construct(Container $container, UserManager $userManager, EntityManagerInterface $em)
    {
        $this->userManager = $userManager;
        $this->container = $container;
        $this->em = $em;
        $this->googleId = $this->container->getParameter('google_id');
        $this->googleSecret = $this->container->getParameter('google_secret');
        $this->googleApiKey = $this->container->getParameter('google_api_key');
        $this->redirectURI = $this->container->getParameter('redirect_uri');

    }

    public function getGoogleToken($code)
    {
        $url = "https://www.googleapis.com/oauth2/v4/token?code=" . $code;
        $postFields = [
            "grant_type" => "authorization_code",
            "client_id" => $this->googleId,
            "client_secret" => $this->googleSecret,
            "apiKey" => $this->googleApiKey,
            "discoveryDocs" => "https://www.googleapis.com/discovery/v1/apis/drive/v3/res",
            "scope" =>  "https://www.googleapis.com/auth/userinfo.profile",
            "redirect_uri" => $this->redirectURI
        ];
        $result = $this->makeGoogleRequest($url, "GET", $postFields);
        $data = json_decode($result, true);
        if ($data === null) {
            parse_str($result, $data);
        }
        $data = $data['id_token'];
        return $data;
    }

    public function checkGoogleUser($token)
    {
        $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . $token;
        $result = $this->makeRequest($url);
        $result = json_decode($result, true);
        return $result;
    }

    public function handleGoogleUser($response)
    {

        $googleId = $response['sub'];

        $userManager = $this->userManager;
        /** @var User $user */
        $user = $userManager->findUserBy(['id' => $googleId]);

        /** @var User $fbUser */
        $googleUser = $userManager->findUserByUsername('google_' . $googleId);
        if (isset($user)) {

            if ($user->username === 'google_' . $googleId) {
                return $user;
            }

            $user->username = 'google_' . $googleId;
            $user->setRolesRaw(['ROLE_USER']);
            $user->setEmail($user->username . '@volunteer.com');
            $userManager->updateUser($user, true);
            return $user;
        }
        if (isset($googleUser)) {
            return $googleUser;
        }

        /** @var User $user */
        $user = new User();

        $user->setRolesRaw(['ROLE_USER']);
        $user->username = 'google_' . $googleId;
        $user->fullName = $response['name'];
        $user->setPassword('false');
        $user->setEmail($user->username . '@volunteer.com');
        $user->setEnabled(true);
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    private function makeRequest($url): string
    {
        $ch = curl_init();
        $opts = [
            CURLOPT_USERAGENT => 'null google api',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POST => false,
            CURLOPT_URL => $url,
        ];

        curl_setopt_array($ch, $opts);

        return curl_exec($ch);
    }

    private function makeGoogleRequest($url, $method = 'GET', $postFields = [])
    {

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_USERAGENT => 'null google api',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POST => $method === 'POST',
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_URL => $url
        ));
        return curl_exec($ch);
    }

}