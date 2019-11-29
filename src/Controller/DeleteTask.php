<?php

namespace App\Controller;


use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class DeleteTask
{
    public function __invoke(Task $data, EntityManagerInterface $em)
    {
        $data->deleted = true;
        $em->persist($data);
        $em->flush();
        return new Response(json_encode(['message' => 'now the resource is deleted']), 200);
    }

}