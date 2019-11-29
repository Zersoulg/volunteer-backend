<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\{ApiFilter, ApiResource};
use App\BaseBundle\Entity\BaseEntity;
use App\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\{
    CreateModeratingEvent,
    DeleteModeratingEvent,
    UpdateModeratingEvent
};
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\{
    NumericFilter,
    OrderFilter,
    DateFilter,
    SearchFilter
};

/**
 * @ORM\Entity()
 * @ApiResource(
 *     collectionOperations={
 *          "get",
 *          "post"={
 *          "method"="POST",
 *          "controller"=CreateModeratingEvent::class,
 *          },
 *     },
 *     itemOperations={
 *          "get",
 *          "delete"={
 *              "method"="DELETE",
 *              "controller"=DeleteModeratingEvent::class,
 *              "path"="/moderating_events/{id}",
 *          },
 *          "put"={
 *          "method"="PUT",
 *          "controller"=UpdateModeratingEvent::class,
 *          "path"="/moderating_events/{id}",
 *          "denormalization_context"={"groups"={"PutMEvent", "SetMEvent"}},
 *          "normalization_context"={"groups"={"GetMEvent"}},
 *          },
 *     },
 *     attributes={
 *          "normalization_context"={
 *              "groups"={
 *                  "GetMEvent", "GetObjBase",
 *                  "GetObjTask", "GetObjCity",
 *                  "GetObjMEvent", "GetObjUser",
 *                  "GetCategory"
 *              }
 *          },
 *          "denormalization_context"={"groups"={"SetMEvent"}},
 *     }
 * )
 *
 * @ApiFilter(NumericFilter::class, properties={"maxMembers"})
 * @ApiFilter(OrderFilter::class,
 *     properties={
 *          "name": "partial",
 *          "category.name": "partial",
 *          "city.name": "partial",
 *          "creator.username": "partial"
 *     }
 * )
 * @ApiFilter(SearchFilter::class,
 *     properties={
 *          "category.id": "exact",
 *          "city.id": "exact",
 *          "creator.id": "exact"
 *     }
 * )
 * @ApiFilter(DateFilter::class, properties={"dateCreate", "dateUpdate"})
 *
 */

class ModeratingEvent extends BaseEntity
{
    /**
     * @var string $name
     * @ORM\Column(name="name", type="string", nullable=false)
     * @Groups({"GetMEvent","GetObjMEvent", "SetMEvent"})
     */
    public $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\CityBundle\Entity\City", inversedBy="events")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     * @Groups({"GetMEvent"})
     */
    public $city;

    /**
     * @var int $geoNameId
     * @ORM\Column(name="geo_name_id", type="integer", nullable=false)
     * @Groups({"SetMEvent"})
     */
    public $geoNameId;

    /**
     * every Event has one Creator
     * @var ArrayCollection|User[] $creator
     * @ORM\ManyToOne(targetEntity="App\UserBundle\Entity\User", inversedBy="event")
     * @Groups({"GetMEvent"})
     */
    public $creator;

    /**
     * every Event has one Category
     * @var ArrayCollection|Category $category
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="events")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * @Groups({"GetMEvent", "SetMEvent"})
     */
    public $category;

    /**
     * One Moderating Event has many Tasks
     * @var ArrayCollection|Task[] $tasks
     * @ORM\OneToMany(targetEntity="App\Entity\Task", mappedBy="moderatingEvent")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id")
     * @Groups({"GetMEvent", "SetMEvent"})
     */
    public $tasks;

    /**
     * @var integer $maxMembers
     * @ORM\Column(name="max_members", type="integer", nullable=true)
     * @Groups({"GetMEvent", "SetMEvent"})
     */
    public $maxMembers;

    /**
     * @var boolean $deleted
     * @Groups({"GetMEvent"})
     * @ORM\Column(name="deleted",type="boolean",nullable=false)
     */
    public $deleted = false;

}