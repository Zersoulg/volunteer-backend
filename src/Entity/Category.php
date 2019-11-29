<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\{
    ApiFilter,
    ApiResource
};
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\{
    NumericFilter,
    OrderFilter,
    DateFilter,
    SearchFilter
};
use App\Controller\DeleteCategory;

/**
 * @ORM\Entity()
 * @ApiResource(
 *     collectionOperations={
 *          "get",
 *          "post",
 *     },
 *     itemOperations={
 *          "get",
 *          "delete"={
 *              "method"="DELETE",
 *              "controller"=DeleteCategory::class,
 *              "path"="/categories/{id}",
 *          },
 *          "put",
 *     },
 *     attributes={
 *          "normalization_context"={"groups"={"GetCategory", "GetObjCategory", "GetObjEvent", "GetObjBase"}},
 *          "denormalization_context"={"groups"={"SetCategory"}},
 *     }
 * )
 *
 * @UniqueEntity("id")
 * @UniqueEntity("name")
 *
 * @ApiFilter(NumericFilter::class, properties={"id"})
 * @ApiFilter(OrderFilter::class, properties={"name": "partial", "event.name": "partial"})
 * @ApiFilter(SearchFilter::class, properties={"event.id": "exact"})
 * @ApiFilter(DateFilter::class, properties={"dateCreate", "dateUpdate"})
 *
 */

class Category
{
    /**
     * @var integer
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"GetCategory"})
     */
    protected $id;

    /**
     * @var string $name
     * @ORM\Column(name="name", type="string", nullable=false)
     * @Groups({"GetCategory", "SetCategory"})
     */
    public $name;

    /**
     * @var string $description
     * @ORM\Column(name="description", type="string", nullable=true)
     * @Groups({"GetCategory", "SetCategory"})
     */
    public $description;

    /**
     * every Category may have many Events
     * @var ArrayCollection|Event $events
     * @ORM\OneToMany(targetEntity="App\Entity\Event", mappedBy="category")
     * @Groups({"GetObjCategory", "SetCategory"})
     */
    public $events;

    /**
     * @var boolean $deleted
     * @Groups({"GetCategory"})
     * @ORM\Column(name="deleted", type="boolean", nullable=false)
     */
    public $deleted = false;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }
}
