<?php

namespace App\Controller;


use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class DeleteCategory
{
    public function __invoke(Category $data, EntityManagerInterface $em)
    {
        $data->deleted = true;
        $em->persist($data);
        $em->flush();
        return new Response(json_encode(['message' => 'now the resource is deleted']), 200);
    }
}