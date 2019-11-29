<?php

namespace App\Controller;


use App\Entity\{Event, ModeratingEvent};
use App\UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class UpdateModeratingEvent
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function __invoke(ModeratingEvent $data, EntityManagerInterface $em)
    {
        /** @var User $currentUser */
        $currentUserId = $this->security->getUser();
        $isAdmin = $this->security->isGranted('ROLE_ADMIN');

        /** @var  Event $eventsCreator */
        $eventsCreator = $data->creator;

        if ($currentUserId !== $eventsCreator && !$isAdmin){
            throw new AccessDeniedException('Вы не создатель мероприятия');
        }
    }
}
