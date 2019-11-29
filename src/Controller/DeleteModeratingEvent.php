<?php

namespace App\Controller;


use App\Entity\{Event, ModeratingEvent};
use App\UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

class DeleteModeratingEvent
{
    public $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function __invoke(ModeratingEvent $data, EntityManagerInterface $em)
    {
        /** @var User $currentUser */
        $currentUserId = $this->security->getUser();

        /** @var  Event $eventsCreator */
        $eventsCreator = $data->creator;

        if ($currentUserId !== $eventsCreator){
            throw new AccessDeniedException('Вы не создатель мероприятия!');
        }

        $data->deleted = true;
        $em->persist($data);
        $em->flush();
        return new Response(json_encode(['message' => 'now the resource is deleted']), 200);
    }
}