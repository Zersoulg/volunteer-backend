<?php

namespace App\Controller;


use App\Entity\Task;
use App\UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;

class UpdateTasksRequests
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function __invoke(Task $data, Request $request)
    {
        $body = json_decode($request->getContent(),true);

        if (isset($body['extraRating'], $body['successfulMembers'])) {

            if ($body['extraRating'] > 0) {
                $user = $data->successfulMembers->last();
                $userId = $user->getId();
                $ratingBefore = $user->rating;
                $ratingAfter = $ratingBefore + $body['extraRating'];


                $qb = $this->em->createQueryBuilder();
                $qb->update(User::class, 'r')
                    ->set('r.rating', $ratingAfter)
                    ->andWhere('r.id = :userId')
                    ->setParameter('userId', $userId)
                    ->getQuery()->getResult();
            }
            return $data;
        }

        if (isset($body['userRequests'])) {
            if ($data->maxMembers === $data->members->count()) {
                throw new AccessDeniedException('У задания уже максимальное количество участников', 403);
            }

            $user = $data->userRequests->last();
            $tasks = $user->tasks;

            foreach ($tasks as $task) {
                if (!(($task->dateEnd <= $data->date) || ($task->date >= $data->dateEnd))) {
                    throw new AccessDeniedException('У вас уже есть запланированное задание на это время', 403);
                }
            }
            return $data;
        }

        if (isset($body['members']) || isset($body['checkingRequests']) || isset($body['successfulMembers'])) {
            return $data;
        }
    }
}