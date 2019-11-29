<?php

namespace App\Controller;


use App\CityBundle\Services\CityService;
use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CreateEvent
{
    private $cityService;

    public function __construct(CityService $cityService)
    {
        $this->cityService = $cityService;
    }

    public function __invoke(Event $data, EntityManagerInterface $em)
    {
        $event = $data;

        $scriptGroupName = $em->createQueryBuilder()->select('t')->from( Event::class, 't')
            ->andWhere('t.name = :name')
            ->andWhere('t.deleted = false')
            ->andWhere('t.geoNameId = :geoNameId')
            ->setParameter('name', $event->name)
            ->setParameter('geoNameId', $event->geoNameId)
            ->getQuery()->getResult();

        if(!empty($scriptGroupName)) {
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