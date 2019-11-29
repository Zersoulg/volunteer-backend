<?php


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/** @ApiResource(
 *     collectionOperations={
 *          "get"={
 *              "swagger_context"={
 *                  "description"=
 *                      "Это промежуточная таблица для связи ManyToMany с полем progressBar"
 *              }
 *          }
 *     },
 *     itemOperations={"get"},
 *     attributes={
 *          "normalization_context"={"groups"={"GetAchievement", "GetObjUser", "GetFile", "GetProgressBar"}},
 *          "denormalization_context"={"groups"={"SetProgressBar"}},
 *     }
 * )
 * @ORM\Entity()
 * @ORM\Table(name="achievements_users_progressBar")
 */

class AchievementProgressBar
{
    /**
     * @var integer
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * One progress bar for each user
     * @var ArrayCollection|User[] $user
     * @ORM\ManyToOne(targetEntity="App\UserBundle\Entity\User", inversedBy="achievements")
     * @Groups({"GetProgressBar"})
     *
     */
    public $user;

    /**
     * One progress bar for each achievement for each user
     * @var ArrayCollection|Achievement[] $achievement
     * @ORM\ManyToOne(targetEntity="App\Entity\Achievement", inversedBy="progressBar")
     * @Groups({"GetProgressBar"})
     */
    public $achievement;

    /**
     * @var integer $progressBar
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"GetProgressBar"})
     */
    public $progressBar;

    /**
     * each achievement may be completed
     * @var boolean $isComplete
     * @ORM\Column(type="boolean", nullable=false)
     * @Groups({"GetProgressBar"})
     */
    public $isComplete = false;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }
}