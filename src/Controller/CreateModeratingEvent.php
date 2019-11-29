<?php

namespace App\Controller;


use App\CityBundle\Services\CityService;
use App\Entity\ModeratingEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CreateModeratingEvent
{
    private $cityService;

    public function __construct(CityService $cityService)
    {
        $this->cityService = $cityService;
    }

    public function __invoke(ModeratingEvent $data, EntityManagerInterface $em)
    {
        $ModeratingEvent = $data;

        $scriptGroupName = $em->createQueryBuilder()->select('t')->from( ModeratingEvent::class, 't')
            ->andWhere('t.name = :name')
            ->andWhere('t.deleted = false')
            ->andWhere('t.geoNameId = :geoNameId')
            ->setParameter('name', $ModeratingEvent->name)
            ->setParameter('geoNameId', $ModeratingEvent->geoNameId)
            ->getQuery()->getResult();

        if(!empty($scriptGroupName)){

            throw new BadRequestHttpException('Такое мероприятие уже существует в этом городе');
        }

        if(isset($data->geoNameId)){
            $data->city = $this->cityService->setCity($data->geoNameId);
        } else {
            throw new BadRequestHttpException('Укажите город');
        }

        return $data;
    }
}