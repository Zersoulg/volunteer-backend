<?php

namespace App\Controller;


use App\Entity\Event;
use App\UserBundle\Entity\User;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Security;

class UpdateEvent
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function __invoke(Event $data)
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();
        $isAdmin = $this->security->isGranted('ROLE_ADMIN');

        /** @var  Event $eventsCreator */
        $eventsCreator = $data->creator;

        if ($currentUser !== $eventsCreator && !$isAdmin){
            throw new AccessDeniedException('Вы не создатель мероприятия!');
        }

        $places = count($data->members);
        $dif = $data->maxMembers - $places;
        if ($dif <= 0) {
            throw new HttpException(400, 'Достингнуто максимальное количество участников!');
        }
        return $data;
    }
}
