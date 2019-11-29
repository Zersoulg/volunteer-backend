<?php

namespace App\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\BaseBundle\Entity\BaseEntity;

/**
 * @ORM\Entity
 *
 */
class Attempt  extends BaseEntity
{

    public $action;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", nullable=true)
     */
    public $ip;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", nullable=true)
     */
    public $username;

    /**
     * @var integer
     *
     * @ORM\Column(name="count", type="integer", nullable=true)
     * @Groups({"getUser", "getObjectUser"})
     */
    public $count = 0;

    /**
     * Attempt constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->dateCreate = new \DateTime();
    }
}
