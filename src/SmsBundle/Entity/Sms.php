<?php

namespace App\SmsBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\BaseBundle\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\SmsBundle\Controller\SmsDebug;
use App\UserBundle\Entity\User;
/**
 *
 * @ORM\Entity
 * @ApiResource(
 *     collectionOperations={
 *       "current"={
 *           "method"="GET",
 *           "path"="/sms/debug",
 *           "controller"=SmsDebug::class,
 *       },
 *     },
 *     normalizationContext={"groups"={"GetSms"}},
 *     denormalizationContext={"groups"={"SetSms"}},
 * )
 */
class Sms extends BaseEntity
{

    /**
     * @var string $phone
     * @ORM\Column(type="string", name="phone")
     * @Groups({"SetSms", "GetSms"})
     */
    public $phone;

    /**
     * @var string $message
     * @ORM\Column(type="string", name="message")
     * @Groups({"SetSms", "GetSms"})
     */
    public $message;

    /**
     * @var string $status
     * @ORM\Column(type="string", name="status")
     * @Groups({"SetSms", "GetSms"})
     */
    public $status;

    /**
     * @var User $user
     * @ORM\ManyToOne(targetEntity="App\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"SetSms", "GetSms"})
     */
    public $user;


}