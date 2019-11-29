<?php
/**
 * Created by PhpStorm.
 * User: vdaron
 * Date: 22.04.2018
 * Time: 20:27
 */

namespace App\BaseBundle\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

class BaseEntity
{

    /**
     * @var integer
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"GetBase", "GetObjBase"})
     */
    protected $id;

    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank
     * @Groups({"GetBase", "GetObjBase"})
     */
    protected $dateCreate;

    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank
     * @Groups({"GetBase", "GetObjBase"})
     */
    protected $dateUpdate;

    public function __construct()
    {
        try {
            $this->dateCreate = new DateTimeImmutable();
            $this->dateUpdate = new DateTimeImmutable();
        } catch (Exception $e) {

        }
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param DateTimeInterface $dateUpdate
     */
    public function setDateUpdate(DateTimeInterface $dateUpdate): void
    {
        $this->dateUpdate = $dateUpdate;
    }


    /**
     * @return  DateTimeInterface|null
     */
    public function getDateUpdate(): ?DateTimeInterface
    {
        return $this->dateUpdate;
    }

    /**
     * @return  DateTimeInterface|null
     */
    public function getDateCreate(): ?DateTimeInterface
    {
        return $this->dateCreate;
    }
}