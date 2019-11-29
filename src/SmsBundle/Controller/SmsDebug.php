<?php

namespace App\SmsBundle\Controller;

use App\SmsBundle\Entity\Sms;
use App\SmsBundle\Services\SendSmsService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


class SmsDebug
{

    private $em;


    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Request $request
     * @param SendSmsService $smsSender
     * @return JsonResponse
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function __invoke(Request $request, SendSmsService $smsSender): JsonResponse
    {
        $sms = $this->em->createQueryBuilder()
            ->select('x')
            ->from(Sms::class,'x')
            ->orderBy('x.dateCreate','desc')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
        return new JsonResponse($sms,200);
    }

}
