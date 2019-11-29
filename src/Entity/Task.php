<?php

namespace App\Entity;

use App\BaseBundle\Entity\BaseEntity;
use App\UserBundle\Entity\User;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\{
    ApiFilter,
    ApiResource
};
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\{
    NumericFilter,
    OrderFilter,
    DateFilter,
    SearchFilter
};
use App\Controller\{
    DeleteTask,
    CreateTask,
    UpdateTasksRequests
};

/**
 * @ORM\Entity()
 * @ApiResource(
 *     collectionOperations={
 *          "get",
 *          "post"={
 *          "method"="POST",
 *          "controller"=CreateTask::class,
 *          },
 *     },
 *     itemOperations={
 *          "get",
 *          "delete"={
 *              "method"="DELETE",
 *              "controller"=DeleteTask::class,
 *              "path"="/tasks/{id}",
 *          },
 *          "put",
 *          "update_tasks_requests"={
 *              "method"="PUT",
 *              "controller"=UpdateTasksRequests::class,
 *              "path"="/tasks/{id}/update_tasks_requests",
 *              "denormalization_context"={"groups"={"CheckRequest"}},
 *              "swagger_context"={
 *                      "description"="
 * Чтобы добавить юзера в таск, нужно оставить только нужный параметр, куда вместо {} вставить iri юзера;
 * !!!ВАЖНО!!! при изменении полей, они меняются ПОЛЬНОСТЬЮ на то, что мы передали в body,
 * поэтому, чтобы не потерять прошлые записи, их тоже следует передать вместе с новым.
 * Если хотим добавить дополнительный рейтинг, то вместе с successfulMembers передаём extraRating - он добавится к текущему рейтингу,
 * если не нужен дополнительный рейтинг, то не указываем его либо указываем 0;
 * рейтинг прибавится только последнему юзеру, поэтому доп.рейтинг можем передать только один раз одному юзеру"
 *              },
 *          },
 *     },
 *     attributes={
 *          "normalization_context"={
 *              "groups"={
 *                  "GetTask", "GetObjTask",
 *                  "GetObjUser", "GetObjEvent",
 *                  "SetTask", "GetObjEvent",
 *                  "GetObjBase"
 *              }
 *          },
 *          "denormalization_context"={"groups"={"SetTask", "CheckRequest"}},
 *     }
 * )
 * @ApiFilter(NumericFilter::class, properties={"id", "difficulty"})
 * @ApiFilter(OrderFilter::class,
 *     properties={
 *          "name": "partial",
 *          "description": "partial",
 *          "event.name": "partial",
 *          "members.username": "partial",
 *          "successfulMembers.username": "partial"
 *     }
 * )
 * @ApiFilter(DateFilter::class, properties={"dateCreate", "dateUpdate"})
 * @ApiFilter(SearchFilter::class,
 *     properties={
 *          "event.id": "exact",
 *          "members.id": "exact",
 *          "successfulMembers.id": "exact"
 *     }
 * )
 *
 */

class Task extends BaseEntity
{
    /**
     * Many Tasks have one Event
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="tasks")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     * @Groups({"GetTask", "SetTask"})
     */
    public $event;

    /**
     * Many Tasks have one Moderating Event
     * @ORM\ManyToOne(targetEntity="App\Entity\ModeratingEvent", inversedBy="tasks")
     * @ORM\JoinColumn(name="moderating_event_id", referencedColumnName="id")
     * @Groups({"GetTask", "SetTask"})
     */
    public $moderatingEvent;

    /**
     * @var string $name
     * @ORM\Column(name="name", type="string", nullable=false)
     * @Groups({"GetTask", "GetObjTask", "SetTask"})
     */
    public $name;

    /**
     * @var string $description
     * @ORM\Column(name="description", type="string", nullable=true)
     * @Groups({"GetTask", "SetTask", "GetObjTask"})
     */
    public $description;

    /**
     * @var integer $difficulty
     * @ORM\Column(name="difficulty", type="integer", nullable=false)
     * @Groups({"GetTask", "GetObjTask", "SetTask"})
     */
    public $difficulty;

    /**
     * Many Tasks have many User Requests
     * @var ArrayCollection|User[] $userRequests
     * @ORM\ManyToMany(targetEntity="App\UserBundle\Entity\User", mappedBy="taskRequests")
     * @Groups({"GetTask", "CheckRequest"})
     */
    public $userRequests;

    /**
     * @var integer $countOfUserRequests
     * @Groups({"GetTask"})
     */
    public $countOfUserRequests;

    /**
     * Many Tasks have many Users
     * @var ArrayCollection|User[] $members
     * @ORM\ManyToMany(targetEntity="App\UserBundle\Entity\User", mappedBy="tasks")
     * @Groups({"GetTask", "CheckRequest"})
     */
    public $members;

    /**
     * @var integer $countOfMembers
     * @Groups({"GetTask"})
     */
    public $countOfMembers;

    /**
     * Many Tasks have many CheckingRequests
     * @var ArrayCollection|User[] $checkingRequests
     * @ORM\ManyToMany(targetEntity="App\UserBundle\Entity\User", mappedBy="requestsOnChecking")
     * @Groups({"GetTask", "CheckRequest"})
     */
    public $checkingRequests;

    /**
     * @var integer $countOfCheckingRequests
     * @Groups({"GetTask"})
     */
    public $countOfCheckingRequests;

    /**
     * Many Tasks have many Successful Members (members who ended task)
     * @var ArrayCollection|User[] $successfulMembers
     * @ORM\ManyToMany(targetEntity="App\UserBundle\Entity\User", mappedBy="successfulTasks")
     * @Groups({"GetTask", "CheckRequest"})
     */
    public $successfulMembers;

    /**
     * @var integer $countOfSuccessfulMembers
     * @Groups({"GetTask"})
     */
    public $countOfSuccessfulMembers;

    /**
     * @var DateTime $deadline
     * @ORM\Column(name="date", type="datetime", nullable=false)
     * @Groups({"GetTask", "GetObjTask", "SetTask"})
     */
    public $date;

    /**
     * @var DateTime $dateEnd
     * @ORM\Column(name="date_end", type="datetime", nullable=false)
     * @Groups({"GetTask", "GetObjTask", "SetTask"})
     */
    public $dateEnd;

    /**
     * @var integer $maxMembers
     * @ORM\Column(name="max_members", type="integer", nullable=true)
     * @Groups({"GetTask", "SetTask"})
     */
    public $maxMembers;

    /**
     * @var boolean $deleted
     * @Groups({"GetTask"})
     * @ORM\Column(name="deleted", type="boolean", nullable=false)
     */
    public $deleted = false;

    /**
     * @var integer $freeSpots
     * @Groups({"GetTask"})
     */
    public $freeSpots;

    /**
     * @var integer $extraRating
     * @Groups({"CheckRequest"})
     */
    public $extraRating;


    public function getFreeSpots()
    {
        $count = $this->members->count();
        $this->freeSpots = $this->maxMembers - $count;

        return $this->freeSpots;
    }

    public function addUserRequest(User $user): Task
    {
        if (!$this->userRequests->contains($user)) {
            $this->userRequests[] = $user;
            $user->taskRequests->add($this);
        }

        return $this;
    }
    public function removeUserRequest(User $user): Task
    {
        if ($this->userRequests->contains($user)) {
            $this->userRequests->removeElement($user);
            $user->taskRequests->removeElement($this);
        }

        return $this;
    }
    public function getCountOfUserRequests(): int
    {
        $count = $this->userRequests->count();
        $this->countOfUserRequests = $count;

        return $this->countOfUserRequests;
    }


    public function addMember(User $user): Task
    {
        if (!$this->members->contains($user)) {
            $this->members[] = $user;
            $user->tasks->add($this);
        }

        return $this;
    }
    public function removeMember(User $user): Task
    {
        if ($this->members->contains($user)) {
            $this->members->removeElement($user);
            $user->tasks->removeElement($this);
        }

        return $this;
    }
    public function getCountOfMembers(): int
    {
        $count = $this->members->count();
        $this->countOfMembers = $count;

        return $this->countOfMembers;
    }


    public function addCheckingRequest(User $user): Task
    {
        if (!$this->checkingRequests->contains($user)) {
            $this->checkingRequests[] = $user;
            $user->requestsOnChecking->add($this);
        }

        return $this;
    }
    public function removeCheckingRequest(User $user): Task
    {
        if ($this->checkingRequests->contains($user)) {
            $this->checkingRequests->removeElement($user);
            $user->requestsOnChecking->removeElement($this);
        }

        return $this;
    }
    public function getCountOfRequestsOnChecking(): int
    {
        $count = $this->checkingRequests->count();
        $this->countOfCheckingRequests = $count;

        return $this->countOfCheckingRequests;
    }


    public function addSuccessfulMember(User $user): Task
    {
        if (!$this->successfulMembers->contains($user)) {
            $this->successfulMembers[] = $user;
            $user->successfulTasks->add($this);
        }
        return $this;
    }
    public function removeSuccessfulMember(User $user): Task
    {
        if ($this->successfulMembers->contains($user)){
            $this->successfulMembers->removeElement($user);
            $user->successfulTasks->removeElement($this);
        }

        return $this;
    }
    public function getCountOfSuccessfulMembers(): int
    {
        $count = $this->successfulMembers->count();
        $this->countOfSuccessfulMembers = $count;

        return $this->countOfSuccessfulMembers;
    }
}