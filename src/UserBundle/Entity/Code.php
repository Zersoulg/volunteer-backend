<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 24.12.18
 * Time: 13:56
 */

namespace App\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\BaseBundle\Entity\BaseEntity;

/**
 * Class Code
 * @package App\BaseBundle\Entity
 * @ORM\Entity()
 * @ORM\Table(name="code")
 */
class Code extends BaseEntity
{
    /**
     * @var string $code
     * @ORM\Column(nullable=false)
     */
    public $code;
    /**
     * @var User $user
     * @ORM\ManyToOne(targetEntity="User", inversedBy="codes")
     * @ORM\JoinColumn(nullable=false)
     */
    public $user;
}
