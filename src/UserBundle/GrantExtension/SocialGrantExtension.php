<?php

namespace App\UserBundle\GrantExtension;

use App\UserBundle\OAuth2\Services\FbService;
use App\UserBundle\OAuth2\Services\VkService;
use App\UserBundle\OAuth2\Services\GoogleService;
use Exception;
use FOS\OAuthServerBundle\Storage\GrantExtensionInterface;
use OAuth2\Model\IOAuth2Client;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use OAuth2\OAuth2ServerException;
use OAuth2\OAuth2;
use Symfony\Bridge\Monolog\Logger;

class SocialGrantExtension implements GrantExtensionInterface
{

    protected $userProvider;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var VkService
     */
    protected $vkService;

    /**
     * @var FbService
     */
    protected $fbService;

    /**
     * @var GoogleService
     */
    protected $googleService;

    public function __construct($container, $vkService, $fbService, $googleService)
    {
        $this->container = $container;
        $this->vkService = $vkService;
        $this->fbService = $fbService;
        $this->googleService = $googleService;

    }

    /**
     * @param IOAuth2Client $client
     * @param array $inputData
     * @param array $authHeaders
     * @return array|bool|null
     * @throws OAuth2ServerException
     */
    public function checkGrantExtension(IOAuth2Client $client, array $inputData, array $authHeaders)
    {
        $socialType = @$inputData['social_type'];
        $token = @$inputData['token'];
        $code = @$inputData['code'];
        $user_id = @$inputData['user_id'];
        $secretCode = @$inputData['session_secret_key'];
        $id = @$inputData['id'];

        if ($socialType === 'vk') {
            return $this->checkVkToken($token, $user_id, $id);
        }

        if ($socialType === 'fb') {
            return $this->checkFbToken($token, $id);
        }

        if ($socialType === 'google') {
            return $this->checkGoogleToken($code);
        }
        throw new OAuth2ServerException((int)OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_REQUEST, 'social type is missing or invalid or not supported yet');
    }

    /**
     * @param $token
     * @param $user_id
     * @param $id
     * @return array
     * @throws OAuth2ServerException
     * @throws Exception
     */
    private function checkVkToken($token, $user_id, $id): ?array
    {
        /** @var VkService $vkService */
        $vkService = $this->vkService;
        if (isset($token)) {
            //Проверка токена
            $result = $vkService->checkVkUserToken($token, $user_id);
            //Получение данных
            $user = $vkService->handleVkUser($result, $id);
            if (is_object($user)) {
                return ['data' => $user];
            }

            throw new OAuth2ServerException((int)OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_GRANT, 'vk token is invalid or expired');
        }

        throw new OAuth2ServerException((int)OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_REQUEST, 'vk token is missing');
    }

    /**
     * @param $token
     * @param $id
     * @return array
     * @throws OAuth2ServerException
     */
    private function checkFbToken($token, $id): array
    {
        /** @var FbService $fbService */
        $fbService = $this->fbService;
        $response = $fbService->checkFbUserToken($token);
        if (isset($response['data']['user_id'])) {
            $inputData = $fbService->getUserInfo($token);
            $user = $fbService->handleFbUser($response, $id, $inputData);
            return ['data'=> $user];
        }

        $this->logger->error('fb token is invalid', [
            'response' => json_encode($response),
        ]);
        throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_GRANT, 'fb token is invalid');

    }

    /**
     * @param $token
     * @return array
     */
    private function checkGoogleToken($token)
    {
        /** @var GoogleService $googleService */
        $googleService = $this->googleService;
        $response = $this->googleService->getGoogleToken($token);
        if (isset($response)) {
            $getUserInfo  = $googleService->checkGoogleUser($response);
            $user = $googleService->handleGoogleUser($getUserInfo);
            if (is_object($user)) {
                return ['data' => $user];
            }
        }
    }

    /**
     * Executes request on link.
     * @param   string $url
     * @param   string $method
     * @param   array $postFields
     * @return  string
     */
    private function makeRequest($url, $method = 'GET', $postFields = [])
    {

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_USERAGENT => 'null vk api',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POST => $method === 'POST',
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_URL => $url
        ));
        return curl_exec($ch);
    }
}
