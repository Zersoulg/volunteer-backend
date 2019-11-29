<?php

namespace App\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 */
class Captcha extends BaseEntity
{

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string")
     */
    public $file;

    /**
     * @var string
     *
     * @ORM\Column(name="phrase", type="string")
     */
    public $phrase;

    /**
     * @var string
     *
     * @ORM\Column(name="method", type="string", nullable=true)
     */
    public $method;

    public function __construct()
    {
        parent::__construct();
        $this->dateCreate = new \DateTime();
    }
}
