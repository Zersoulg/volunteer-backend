<?php

namespace App\UserBundle\OAuth2\Services;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use FOS\UserBundle\Doctrine\UserManager;
use App\UserBundle\Entity\User;


class VkService
{

    /** @var mixed */
    private $vkId;

    private $em;

    /** @var string */
    private $vkSecret;

    /** @var UserManager */
    private $userManager;

    /** @var Container */
    private $container;

    const VK_API_VERSION = '5.80';

    public function __construct(Container $container, UserManager $userManager, EntityManagerInterface $em)
    {
        $this->userManager = $userManager;
        $this->container = $container;
        $this->em = $em;
        $this->vkId = $this->container->getParameter('vk_id');
        $this->vkSecret = $this->container->getParameter('vk_secret');
    }

    /**
     * @param $token
     * @param $user_id
     * @return mixed|string
     */
    public function checkVkUserToken($token, $user_id)
    {
        $url = 'https://api.vk.com/method/users.get?';
        $url .= http_build_query([
            'user_ids' => $user_id,
            'v' => self::VK_API_VERSION,
            'access_token' => $token
        ]);
        $result = $this->makeRequest($url);
        $result = json_decode($result, true);
        return $result;
    }


    /**
     * @param $response
     * @param $id
     * @return User|UserInterface|null|object
     * @throws Exception
     */
    public function handleVkUser($response, $id)
    {
        $userManager = $this->userManager;
        $user = $userManager->findUserBy(['id' => $id]);
        $vkUser = $userManager->findUserBy(['username' => 'vk_' . $response['response'][0]['id']]);
        if (isset($user)) {

            if (isset($vkUser) && $vkUser !== $user) {
                return $vkUser;
            }

            if ($user->username === 'vk_' . $response['response'][0]['id']) {
                return $user;
            }

            $user->username = 'vk_' . $response['response'][0]['id'];

            $user->setEmail($user->username . '@volunteer.com');
            $user->setEnabled(true);
            $user->setPassword('false');
            $userManager->updateUser($user, true);
            return $user;
        }

        if (isset($vkUser)) {
            return $vkUser;
        }
        $user = new User();

        $user->setRolesRaw(['ROLE_USER']);
        $user->fullName = $response['response'][0]['first_name'] . " " . $response['response'][0]['last_name'];
        $user->username = 'vk_' . $response['response'][0]['id'];
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
     * @param string $method
     * @param array $postFields
     * @return  string
     */
    private function makeRequest($url, $method = 'GET', $postFields = []): string
    {

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_USERAGENT => 'null vk api',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POST => ($method == 'POST'),
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_URL => $url
        ));
        return curl_exec($ch);
    }


}
