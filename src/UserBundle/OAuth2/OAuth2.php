<?php
/**
 * Created by PhpStorm.
 * User: kwant
 * Date: 21.08.18
 * Time: 15:06
 */

namespace App\UserBundle\OAuth2;

    use App\BaseBundle\Entity\Attempt;
use App\BaseBundle\Entity\Captcha;
use Gregwar\CaptchaBundle\Generator\CaptchaGenerator;
use OAuth2\IOAuth2Storage;
use OAuth2\OAuth2 as BaseOAuth;
use
    OAuth2\Model\IOAuth2Client;
use OAuth2\IOAuth2GrantUser;
use OAuth2\OAuth2ServerException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManager;


class OAuth2 extends BaseOAuth
{

    /**
     * The provided authorization grant is invalid, expired,
     * revoked, does not match the redirection URI used in the
     * authorization request, or was issued to another client.
     *
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-5.2
     */
    const WARNING_USE_TWO_FACTOR = 'two_factor_required';

    protected $authCodeLifetime = 3600;

    protected $em;

    protected $mailer;
    private $generator;
    private $container;

    /**
     * OAuth2 constructor.
     * @param IOAuth2Storage $storage
     * @param array $config
     * @param EntityManager|null $em
     * @param \Swift_Mailer|null $mailer
     * @param CaptchaGenerator $generator
     * @param Container $container
     */
    public function __construct(IOAuth2Storage $storage, array $config = array(), ?EntityManager $em = null, ?\Swift_Mailer $mailer = null, CaptchaGenerator $generator, Container $container)
    {
        parent::__construct($storage, $config);
        if (isset($config['auth_code_life'])) {
            $this->authCodeLifetime = $config['auth_code_life'];
        }
        $this->container = $container;
        $this->em = $em;
        $this->mailer = $mailer;
        $this->generator = $generator;
    }


    /**
     * @param IOAuth2Client $client
     * @param array $input
     *
     * @return array|bool
     * @throws OAuth2ServerException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    protected function grantAccessTokenUserCredentials(IOAuth2Client $client, array $input)
    {
        if (!($this->storage instanceof IOAuth2GrantUser)) {
            throw new OAuth2ServerException(Response::HTTP_BAD_REQUEST, self::ERROR_UNSUPPORTED_GRANT_TYPE);
        }

        if (!$input['username'] || !$input['password']) {
            throw new OAuth2ServerException(Response::HTTP_BAD_REQUEST, self::ERROR_INVALID_REQUEST, 'Missing parameters. "username" and "password" required');
        }

        $stored = $this->storage->checkUserCredentials($client, $input['username'], $input['password']);
        $ip = $_SERVER['REMOTE_ADDR'];

        $attempt = $this->em->getRepository(Attempt::class)->findOneBy(['ip' => $ip, 'username' => $input['username']]);
        if (isset($attempt)) {
            if ($attempt->count >= 5) {
                $captcha = $this->respCaptcha();
                throw new OAuth2ServerException(429, 'captcha', $captcha);
            }
        }
        if ($stored === false) {

            if (!isset($attempt)) {
                $attempt = new Attempt();
                $attempt->username = $input['username'];
                $attempt->ip = $ip;
            }


            $attempt->count++;
            $this->em->persist($attempt);
            $this->em->flush();
            throw new OAuth2ServerException(Response::HTTP_BAD_REQUEST, self::ERROR_INVALID_GRANT, 'Invalid username and password combination');
        }


        return $stored;
    }

    private function respCaptcha(): array
    {
        $backgroundImages = $this->container->getParameter('background_images');
        $font = $this->container->getParameter('font');
        $config['distortion'] = true;
        $config['max_front_lines'] = 2;
        $config['max_behind_lines'] = 0;
        $config['interpolation'] = true;
        $config['ignore_all_effects'] = true;
        $config['width'] = 600;
        $config['height'] = 200;
        $config['length'] = 5;
        $config['background_images'] = [$backgroundImages];
        $config['font'] = $font;
        $config['keep_value'] = true;
        $config['as_file'] = true;
        $config['charset'] = 'abcdefhjkmnprstuvwxyz23456789';
        $phrase = $this->generator->getPhrase($config);
        $this->generator->setPhrase($phrase);
        $file = $this->generator->generate($config);
        $file = explode('/', $file)[2];

        $captcha = new Captcha();
        $captcha->file = $file;
        $captcha->phrase = $phrase;
        $this->em->persist($captcha);
        $this->em->flush();

        return ['file' => $file];
    }
}
