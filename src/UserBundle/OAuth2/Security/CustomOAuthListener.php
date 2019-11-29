<?php

declare(strict_types=1);

namespace App\UserBundle\OAuth2\Security;

use FOS\OAuthServerBundle\Security\Firewall\OAuthListener;
use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use OAuth2\OAuth2AuthenticateException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;


class CustomOAuthListener extends OAuthListener
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param GetResponseEvent $event the event
     * @return void|null
     * @throws OAuth2AuthenticateException
     */
    public function handle(GetResponseEvent $event)
    {
        if (null === $oauthToken = $this->serverService->getBearerToken($event->getRequest(), true)) {
            return;
        }

        $token = new OAuthToken();
        $token->setToken($oauthToken);

        try {
            $returnValue = $this->authenticationManager->authenticate($token);

            if ($returnValue instanceof TokenInterface) {
                return $this->tokenStorage->setToken($returnValue);
            }

            if ($returnValue instanceof Response) {
                return $event->setResponse($returnValue);
            }
        } catch (AuthenticationException $e) {
            $prev = $e->getPrevious();
            if($prev instanceof OAuth2AuthenticateException){
                // TODO: find better way to check this
                if($prev->getDescription() === "User account is disabled."){
                    // TODO: pass user here
                    $event->getRequest()->attributes->set('disabled', true);
                    return null;
                }
            }
            if (null !== $p = $e->getPrevious()) {
                $event->setResponse($p->getHttpResponse());
            }
        }
    }
}
