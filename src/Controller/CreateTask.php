<?php

namespace App\Controller;


use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class CreateTask
{
    public function __invoke(Task $data, EntityManagerInterface $em)
    {
        $task = $data;

        $scriptGroupName = $em->createQueryBuilder()->select('t')->from( Task::class, 't')
            ->andWhere('t.name = :name')
            ->andWhere('t.deleted = false')
            ->andWhere('t.event = :event')
            ->setParameter('name', $task->name)
            ->setParameter('event', $task->event)
            ->getQuery()->getResult();

        if(!empty($scriptGroupName)){
            return new Response(json_encode(['ms'=>'Такое задание уже существует у мероприятия']), 400);
        }

        return $data;
    }
}