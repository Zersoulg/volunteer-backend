<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 10.01.19
 * Time: 12:04
 */

namespace App\UserBundle\OAuth2\Services;

use App\UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Doctrine\UserManager;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class FbService
{
    /** @var mixed */
    private $fbId;

    /** @var string */

    private $fbSecret;

    /** @var UserManager */
    private $userManager;

    /** @var Container */
    private $container;

    private $em;

    public function __construct(Container $container, UserManager $userManager, EntityManagerInterface $em)
    {
        $this->userManager = $userManager;
        $this->container = $container;
        $this->em = $em;
        $this->fbId = $this->container->getParameter('fb_id');
        $this->fbSecret = $this->container->getParameter('fb_secret');
    }

    private function getServerToken()
    {

        $url = 'https://graph.facebook.com/oauth/access_token?';
        $url .= http_build_query([
            'client_id' => $this->fbId,
            'client_secret' => $this->fbSecret,
            'grant_type' => 'client_credentials',
        ]);
        $result = $this->makeRequest($url);
        $data = json_decode($result, true);
        if ($data === null) {
            parse_str($result, $data);
        }
        return $data['access_token'];
    }

    /**
     * @param string $token - token of fb user
     * @return mixed|string
     */
    public function checkFbUserToken($token)
    {
        $serverToken = $this->getServerToken();
        $url = 'https://graph.facebook.com/debug_token?';
        $url .= http_build_query([
            'access_token' => $serverToken,
            'input_token' => $token,
        ]);
        $result = $this->makeRequest($url);
        $result = json_decode($result, true);
        return $result;
    }

    public function getUserInfo($token)
    {
        $url = 'https://graph.facebook.com/me?fields=id,name&access_token=' . $token;
        $data = $this->makeRequest($url);
        $data = json_decode($data, true);
        return $data;
    }

    public function handleFbUser($response, $id, $inputData): User
    {
        $fbId = $response['data']['user_id'];

        $userManager = $this->userManager;
        /** @var User $user */
        $user = $userManager->findUserBy(['id' => $id]);

        /** @var User $fbUser */
        $fbUser = $userManager->findUserByUsername('fb_' . $fbId);
        if (isset($user)) {

            if ($user->username === 'fb_' . $fbId) {
                return $user;
            }

            $user->username = 'fb_' . $fbId;
            $user->setRolesRaw(['ROLE_USER']);
            $user->setEmail($user->username . '@volunteer.com');
            $userManager->updateUser($user, true);
            return $user;
        }
        if (isset($fbUser)) {
            return $fbUser;
        }

        /** @var User $user */
        $user = new User();

        $user->setRolesRaw(['ROLE_USER']);
        $user->username = 'fb_' . $fbId;
        $user->fullName = $inputData['name'];
        $user->setPassword('false');
        $user->setEmail($user->username . '@volunteer.com');
        $user->setEnabled(true);
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }


    /**
     * Executes request on link.
     * @param string $url
     * @return  string
     */
    private function makeRequest($url): string
    {
        $ch = curl_init();
        $opts = [
            CURLOPT_USERAGENT => 'null fb api',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POST => false,
            CURLOPT_URL => $url,
        ];

        curl_setopt_array($ch, $opts);

        return curl_exec($ch);
    }
}