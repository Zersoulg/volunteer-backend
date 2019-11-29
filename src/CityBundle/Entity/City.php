<?php


namespace App\CityBundle\Entity;

use ApiPlatform\Core\Annotation\{ApiFilter, ApiResource};
use App\BaseBundle\Entity\BaseEntity;
use App\Entity\Event;
use App\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\{NumericFilter, OrderFilter, DateFilter, SearchFilter};
use Doctrine\ORM\Mapping as ORM;
use App\CityBundle\Controller\GetGeoNameCity;


/**
 * @ORM\Entity()
 * @ApiResource(
 *     collectionOperations={
 *          "getGeoNameCity"={
 *              "method"="GET",
 *              "controller"=GetGeoNameCity::class,
 *              "path"="/cities/geoname_cities",
 *              "swagger_context"={
 *                 "parameters"={
 *                     {"name"="name", "in"="query", "type"="string"},
 *                 },
 *              }
 *          },
 *          "get"={
 *                  "normalization_context"={"groups"={"GetObjCity", "GetObjBase"}}
 *          },
 *          "post"={
 *              "swagger_context"={
 *                  "description"="
  Вспомогательный метод для получения списка городов из стороннего сервиса
 * При создании города или юзера происходит проверка в базе городов по geonameId
 * Если в нашей базе нет города с таким geonameId, он создаётся в базе и присваивается юзеру или ивенту"
 *              }
 *          },
 *     },
 *     itemOperations={
 *          "get",
 *          "delete",
 *          "put"
 *     },
 *     attributes={
 *          "normalization_context"={"groups"={"GetCity", "GetObjBase"}},
 *          "denormalization_context"={"groups"={"SetCity"}},
 *     }
 * )
 * @ApiFilter(NumericFilter::class, properties={"id", "geoNameId"})
 * @ApiFilter(OrderFilter::class, properties={"name": "partial", "event.name": "partial", "user.username": "partial"})
 * @ApiFilter(SearchFilter::class, properties={"event.id": "exact", "user.id": "exact"})
 * @ApiFilter(DateFilter::class, properties={"dateCreate", "dateUpdate"})
 *
 * @UniqueEntity("geoNameId")
 */
class City extends BaseEntity
{
    public function __construct() {
        parent::__construct();
        $this->events = new ArrayCollection();
    }

    /**
     * @var int $geoNameId
     * @ORM\Column(type="integer", nullable=false)
     * @Groups({"GetCity", "GetObjCity", "SetCity"})
     */
    public $geoNameId;

    /**
     * @var string $name
     * @ORM\Column(name="name", type="string", nullable=false)
     * @Groups({"GetCity","GetObjCity", "SetCity"})
     */
    public $name;

    /**
     * @var ArrayCollection|Event[] $events
     * @ORM\OneToMany(targetEntity="App\Entity\Event", mappedBy="city")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    public $events;

    /**
     * @var ArrayCollection|User[] $users
     * @ORM\OneToMany(targetEntity="App\UserBundle\Entity\User", mappedBy="city")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    public $users;

    /**
     * @var boolean $deleted
     * @Groups({"GetCity"})
     * @ORM\Column(name="deleted", type="boolean", nullable=false)
     */
    protected $deleted = false;
}