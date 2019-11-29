<?php

namespace App\Controller;


use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class DeleteEvent
{
    public function __invoke(Event $data, EntityManagerInterface $em)
    {
        $data->deleted = true;
        $em->persist($data);
        $em->flush();
        return new Response(json_encode(['message' => 'now the resource is deleted']), 200);
    }
}