<?php


namespace App\Entity;

use ApiPlatform\Core\Annotation\{
    ApiFilter,
    ApiResource
};
use App\FileBundle\Entity\File;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\DeleteAchievement;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\{
    NumericFilter,
    OrderFilter,
    SearchFilter,
    DateFilter
};

/**
 * @ORM\Entity()
 * @ApiResource(
 *     collectionOperations={
 *          "get",
 *          "post"={
 *              "swagger_context"={
 *                  "description"=
 *                      "Чтобы привязать картинку, нужно сначала сделать POST запрос на File, затем указать iri"
 *              }
 *          },
 *     },
 *     itemOperations={
 *          "get",
 *          "delete"={
 *              "method"="DELETE",
 *              "controller"=DeleteAchievement::class,
 *              "path"="/achievements/{id}",
 *          },
 *          "put",
 *     },
 *     attributes={
 *          "normalization_context"={"groups"={"GetAchievement", "GetObjUser", "GetFile", "GetObjBase"}},
 *          "denormalization_context"={"groups"={"SetAchievement"}},
 *     }
 * )
 *
 * @UniqueEntity("id")
 * @UniqueEntity("name")
 *
 * @ApiFilter(NumericFilter::class, properties={"id"})
 * @ApiFilter(OrderFilter::class, properties={"name": "partial", "user.name": "partial"})
 * @ApiFilter(SearchFilter::class, properties={"user.id": "exact"})
 * @ApiFilter(DateFilter::class, properties={"dateCreate", "dateUpdate"})
 *
 */

class Achievement
{
    /**
     * @var integer
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"GetAchievement"})
     */
    protected $id;

    /**
     * @var string $name
     * @ORM\Column(name="name", type="string", nullable=false)
     * @Groups({"GetAchievement", "SetAchievement"})
     */
    public $name;

    /**
     * @var ArrayCollection|AchievementProgressBar[] $progressBar
     * @ORM\OneToMany(targetEntity="App\Entity\AchievementProgressBar", mappedBy="achievement")
     */
    public $progressBar;

    /**
     * @var boolean $deleted
     * @Groups({"GetAchievement"})
     * @ORM\Column(name="deleted",type="boolean",nullable=false)
     */
    public $deleted = false;

    /**
     * @var integer $numberToComplete
     * @ORM\Column(name="number_to_complete", type="integer", nullable=false)
     * @Groups({"GetAchievement", "SetAchievement"})
     */
    public $numberToComplete;

    /**
     * @var File $icon
     * @ORM\OneToOne(targetEntity="App\FileBundle\Entity\File")
     * @Groups({"GetAchievement", "SetAchievement"})
     */
    public $icon;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }
}