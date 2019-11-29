<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 20.12.18
 * Time: 12:21
 */

namespace App\UserBundle\Controller;

use App\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class CurrentUser
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function __invoke(Request $request): ?User
    {
        /** @var User $user */
        $user = $this->security->getUser();

        return $user;
    }

}
