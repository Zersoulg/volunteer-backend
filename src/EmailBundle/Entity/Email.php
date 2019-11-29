<?php

namespace App\EmailBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\BaseBundle\Entity\BaseEntity;
use App\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @ORM\Entity
 * @ApiResource(
 *     normalizationContext={"groups"={"GetEmail"}},
 *     denormalizationContext={"groups"={"SetEmail"}},
 * )
 */
class Email extends BaseEntity
{

    /**
     * @var string $to
     * @ORM\Column(type="string", name="to")
     * @Groups({"SetEmail", "GetEmail"})
     */
    public $to;

    /**
     * @var string $message
     * @ORM\Column(type="string", name="message")
     * @Groups({"SetEmail", "GetEmail"})
     */
    public $message;
    /**
     * @var string $topic
     * @ORM\Column(type="string", name="topic")
     * @Groups({"SetEmail", "GetEmail"})
     */
    public $topic;

    /**
     * @var User $user
     * @ORM\ManyToOne(targetEntity="App\UserBundle\Entity\User",inversedBy="messageEmail",cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"SetEmail", "GetEmail"})
     */
    public $user;


}