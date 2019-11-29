<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 24.12.18
 * Time: 12:06
 */

namespace App\UserBundle\Controller;


use App\UserBundle\Entity\Code;
use App\UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ActivateUser
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function __invoke(Request $request, $id)
    {
        $code = $request->query->get('code');
        if (!isset($code)) {
            throw new HttpException(400, 'Bad code');
        }
        if (!$id) {
            throw new HttpException(400, 'Bad id');
        }
        $codeObject = $this->em->getRepository(Code::class)->findBy(['id' => $id, 'code' => $code]);
        if (isset($codeObject)) {
            /** @var User $user */
            $user = $this->em->getRepository(User::class)->find($id);
            $user->setEnabled(true);
            $this->em->persist($user);
            $this->em->flush();
            return new JsonResponse(['message' => 'success']);
        }
        throw new HttpException(400, 'Invalid code');
    }
}
