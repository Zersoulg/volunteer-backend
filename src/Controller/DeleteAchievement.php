<?php

namespace App\Controller;


use App\Entity\Achievement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class DeleteAchievement
{
    public function __invoke(Achievement $data, EntityManagerInterface $em)
    {
        $data->deleted = true;
        $em->persist($data);
        $em->flush();
        return new Response(json_encode(['message' => 'now the resource is deleted']), 200);
    }
}