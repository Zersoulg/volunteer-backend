<?php

namespace App\Entity;

use App\BaseBundle\Entity\BaseEntity;
use App\UserBundle\Entity\User;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\ {
        NumericFilter,
        OrderFilter,
        DateFilter,
        SearchFilter,
        BooleanFilter
};
use ApiPlatform\Core\Annotation\{
        ApiFilter,
        ApiResource
};
use App\Controller\{
        DeleteEvent,
        CreateEvent,
        UpdateEvent,
        UpdateEventsMembersOrTasks
};

/**
 * @ORM\Entity()
 * @ApiResource(
 *     collectionOperations={
 *         "get",
 *         "post"={
 *             "method"="POST",
 *             "controller"=CreateEvent::class,
 *         },
 *     },
 *     itemOperations={
 *         "get",
 *         "delete"={
 *             "method"="DELETE",
 *             "controller"=DeleteEvent::class,
 *             "path"="/events/{id}",
 *         },
 *          "update_events_members_or_tasks"={
 *              "method"="PUT",
 *              "path"="/events/{id}/update_events_members_or_tasks",
 *              "controller"=UpdateEventsMembersOrTasks::class,
 *              "denormalization_context"={"groups"={"AddMembersAndTasks"}},
 *              "swagger_context"={
 *                      "description"="Метод для добавления участников и тасков мероприятию.
 !!!ВАЖНО!!! при изменении полей, они меняются ПОЛЬНОСТЬЮ на то, что мы передали в body,
 поэтому, чтобы не потерять прошлые записи, их тоже следует передать вместе с новым."
 *              },
 *          },
 *         "put"={
 *             "method"="PUT",
 *             "controller"=UpdateEvent::class,
 *             "path"="/events/{id}",
 *             "denormalization_context"={
 *                  "groups"={
 *                      "SetEvent", "SetTask"
 *                  }
 *              },
 *             "normalization_context"={
 *                  "groups"={
 *                      "GetEvent", "GetObjTask"
 *                  }
 *             },
 *          },
 *     },
 *     attributes={
 *         "normalization_context"={
 *              "groups"={
 *                  "GetEvent", "GetObjBase",
 *                  "GetObjTask", "GetObjCity",
 *                  "GetObjEvent", "GetObjUser",
 *                  "GetCategory", "GetObjModerating",
 *              }
 *          },
 *         "denormalization_context"={"groups"={"SetEvent", "AddMembersAndTasks"}},
 *     }
 * )
 *
 * @ApiFilter(NumericFilter::class, properties={"maxMembers"})
 * @ApiFilter(OrderFilter::class,
 *     properties={
 *          "name": "partial",
 *          "category.name": "partial",
 *          "city.name": "partial",
 *          "tasks.name": "partial",
 *          "creator.username": "partial",
 *          "members.username": "partial",
 *          "acceptedMembers.username": "partial"
 *    }
 * )
 * @ApiFilter(SearchFilter::class,
 *     properties={
 *          "city.id": "exact",
 *          "city.geoNameId": "exact",
 *          "tasks.id": "exact",
 *          "creator.id": "exact",
 *          "category.id": "exact",
 *          "members.id": "exact"
 *     }
 * )
 * @ApiFilter(DateFilter::class,
 *     properties={
 *          "dateCreate",
 *          "dateUpdate",
 *          "date",
 *          "deadline",
 *          "dateEnd"
 *     }
 * )
 * @ApiFilter(BooleanFilter::class,
 *     properties={
 *          "registration",
 *          "inProgress",
 *          "isActual"
 *     }
 * )
 *
 */

class Event extends BaseEntity
{
    /**
     * @var string $name
     * @ORM\Column(name="name", type="string", nullable=false)
     * @Groups({"GetEvent","GetObjEvent", "SetEvent"})
     */
    public $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\CityBundle\Entity\City", inversedBy="events")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     * @Groups({"GetEvent"})
     */
    public $city;

    /**
     * @var int $geoNameId
     * @ORM\Column(name="geo_name_id", type="integer", nullable=false)
     * @Groups({"SetEvent"})
     */
    public $geoNameId;

    /**
     * One Event has many Tasks
     * @var ArrayCollection|Task[] $tasks
     * @ORM\OneToMany(targetEntity="App\Entity\Task", mappedBy="event")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id")
     * @Groups({"GetEvent", "AddMembersAndTasks"})
     */
    public $tasks;

    /**
     * every Event has one Creator
     * @var ArrayCollection|User[] $creator
     * @ORM\ManyToOne(targetEntity="App\UserBundle\Entity\User", inversedBy="event")
     * @Groups({"GetEvent"})
     */
    public $creator;

    /**
     * every Event has one Category
     * @var ArrayCollection|Category $category
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="events")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * @Groups({"GetEvent", "SetEvent"})
     */
    public $category;

    /**
     * @var ArrayCollection|User[] $members
     * @ORM\ManyToMany(targetEntity="App\UserBundle\Entity\User", inversedBy="events")
     * @ORM\JoinTable(name="users_events")
     * @Groups({"GetEvent", "AddMembersAndTasks"})
     */
    public $members;

    /**
     * @var integer $maxMembers
     * @ORM\Column(name="max_members", type="integer", nullable=true)
     * @Groups({"GetEvent", "SetEvent"})
     */
    public $maxMembers;

    /**
     * @var DateTime $deadline
     * @ORM\Column(name="deadline", type="datetime", nullable=true)
     * @Groups({"GetEvent", "SetEvent"})
     */
    public $deadline;

    /**
     * @var DateTime $deadline
     * @ORM\Column(name="date", type="datetime", nullable=true)
     * @Groups({"GetEvent", "SetEvent"})
     */
    public $date;

    /**
     * @var DateTime $dateEnd
     * @ORM\Column(name="date_end", type="datetime", nullable=true)
     * @Groups({"GetEvent", "SetEvent"})
     */
    public $dateEnd;

    /**
     * @var boolean $deleted
     * @Groups({"GetEvent"})
     * @ORM\Column(name="deleted", type="boolean", nullable=false)
     */
    public $deleted = false;

    /**
     * @var bool $registration
     * @Groups({"GetEvent"})
     * @ORM\Column(name="registration", type="boolean", nullable=true)
     */
    public $registration = true;

    /**
     * @var boolean $inProgress
     * @Groups({"GetEvent"})
     * @ORM\Column(name="in_progress", type="boolean", nullable=true)
     */
    public $inProgress = false;

    /**
     * @var bool $isActual
     * @Groups({"GetEvent"})
     * @ORM\Column(name="is_actual", type="boolean", nullable=true)
     */
    public $isActual = true;

    /**
     * @var integer $freeSpots
     * @Groups({"GetEvent"})
     */
    public $freeSpots;

    public function getFreeSpots()
    {
        $count = count($this->members);
        $this->freeSpots = $this->maxMembers - $count;

        return $this->freeSpots;
    }
}