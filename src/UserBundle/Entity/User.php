<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 18.12.18
 * Time: 19:15
 */

namespace App\UserBundle\Entity;


use ApiPlatform\Core\Annotation\{
    ApiFilter,
    ApiResource
};
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\{
    NumericFilter,
    OrderFilter,
    DateFilter,
    SearchFilter
};
use App\Entity\{
    AchievementProgressBar,
    Task
};
use App\FileBundle\Entity\File;
use App\UserBundle\Controller\{
    ActivateUser,
    CreateUser,
    CurrentUser
};
use App\EmailBundle\Entity\Email;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class User
 * @package App\Entity
 * @ORM\Table(name="user")
 * @ORM\Entity()
 * @ApiResource(
 *     collectionOperations={
 *          "get",
 *          "post"={
 *              "controller"=CreateUser::class
 *          },
 *          "current"={
 *              "method"="GET",
 *              "path"="/users/current",
 *              "controller"=CurrentUser::class,
 *              "pagination_enabled"=false,
 *          },
 *     },
 *     itemOperations={
 *          "get",
 *          "put",
 *          "delete",
 *          "activate_user"={
 *              "method"="GET",
 *              "path"="/users/activate/{id}",
 *              "controller"=ActivateUser::class,
 *          }
 *     },
 *     attributes={
 *          "normalization_context"={
 *              "groups"={
 *                  "GetUser", "GetObjUser",
 *                  "GetObjTask", "GetObjCity",
 *                  "GetObjEvent", "GetObjBase",
 *                  "GetAchievement", "GetFile",
 *              }
 *          },
 *          "denormalization_context"={"groups"={"SetUser","SetObjUser"}}
 *     }
 * )
 *
 * @ApiFilter(NumericFilter::class, properties={"id", "rating"})
 * @ApiFilter(OrderFilter::class,
 *     properties={
 *          "username": "partial",
 *          "roles": "partial",
 *          "email": "partial",
 *          "phone": "partial",
 *          "fullName": "partial",
 *          "event.name": "partial",
 *          "tasks.name": "partial",
 *          "city.name": "partial",
 *          "events.name": "partial",
 *          "successfulTasks.name": "partial"
 *     }
 * )
 * @ApiFilter(SearchFilter::class,
 *     properties={
 *          "event.id": "exact",
 *          "tasks.id": "exact",
 *          "city.id": "exact",
 *          "events.id": "exact",
 *          "successfulTasks.id": "exact"
 *     }
 *  )
 * @ApiFilter(DateFilter::class, properties={"dateCreate", "dateUpdate"})
 *
 * @UniqueEntity("username")
 * @UniqueEntity("usernameCanonical")
 * @UniqueEntity("phone")
 * @UniqueEntity("email")
 *
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"GetUser", "GetObjUser"})
     */
    protected $id;

    /**
     * @var string
     */
    protected $usernameCanonical;

    /**
     * @var
     * @Groups({"GetUser"})
     */
    protected $enabled = false;

    /**
     * @Groups({"GetUser", "GetObjUser", "SetUser"})
     * @Assert\Regex("/^[\w\d_@.]{3,20}$/")
     * @Assert\NotBlank()
     */
    public $username;

    /**
     * @var string $password
     * @Groups({"SetUser"})
     * @Assert\NotBlank()
     * @Assert\Length(min="5", minMessage="min 5 symbols")
     */
    protected $password;

    /**
     * @Groups({"GetUser"})
     */
    protected $roles;

    /**
     * @var string $email
     * @Groups({"GetUser", "GetObjUser", "SetUser"})
     * @Assert\Email()
     */
    protected $email;

    /**
     * @var Email $messageEmail
     * @ORM\OneToMany(targetEntity="App\EmailBundle\Entity\Email",mappedBy="user",cascade={"remove"})
     */
    public $messageEmail;

    /**
     * @var string телефон
     * @ORM\Column(name="phone",type="string",nullable=true)
     * @Assert\Regex("/^[\d\W]+$/")
     * @Groups({"GetUser","GetObjUser","SetUser"})
     *
     */
    public $phone;

    /**
     * @var string $fullName
     * @Groups({"GetUser", "GetObjUser", "SetUser"})
     * @ORM\Column(name="full_name", type="string", nullable=false)
     */
    public $fullName;

    /**
     * @var ArrayCollection|Code[] $codes
     * @ORM\OneToMany(targetEntity="Code", mappedBy="user", cascade={"remove"})
     */
    public $codes;

    /**
     * @var int $rating
     * @Groups({"GetUser", "GetObjUser", "SetUser"})
     * @ORM\Column(name="rating", type="integer", nullable=true)
     */
    public $rating;

    /**
     * Many Users have many TaskRequests
     * @var ArrayCollection|Task[] $taskRequests
     * @ORM\ManyToMany(targetEntity="App\Entity\Task", inversedBy="userRequests")
     * @ORM\JoinTable(name="users_tasks_requests")
     * @Groups({"GetUser"})
     */
    public $taskRequests;

    /**
     * Many Users have many Tasks
     * @var ArrayCollection|Task[] $tasks
     * @ORM\ManyToMany(targetEntity="App\Entity\Task", inversedBy="members")
     * @ORM\JoinTable(name="users_tasks")
     * @Groups({"GetUser"})
     */
    public $tasks;

    /**
     * Many Users have many Requests On Checking
     * @var ArrayCollection|Task[] $requestsOnChecking
     * @ORM\ManyToMany(targetEntity="App\Entity\Task", inversedBy="checkingRequests")
     * @ORM\JoinTable(name="users_tasks_checking_requests")
     * @Groups({"GetUser"})
     */
    public $requestsOnChecking;

    /**
     * Many Users have many Successful Tasks (ended tasks)
     * @var ArrayCollection|Task[] $successfulTasks
     * @ORM\ManyToMany(targetEntity="App\Entity\Task", inversedBy="successfulMembers")
     * @ORM\JoinTable(name="successful_users_tasks")
     * @Groups({"GetUser"})
     */
    public $successfulTasks;

    /**
     * @ORM\ManyToOne(targetEntity="App\CityBundle\Entity\City", inversedBy="users")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     * @Groups({"GetUser", "GetObjUser"})
     */
    public $city;

    /**
     * @var int $geoNameId
     * @Groups({"SetUser"})
     */
    public $geoNameId;

    /**
     * one User can create many Events
     * @ORM\OneToMany(targetEntity="App\Entity\Event", mappedBy="creator")
     */
    public $event;

    /**
     * @var ArrayCollection|User[] $events
     * @ORM\ManyToMany(targetEntity="App\Entity\Event", mappedBy="members")
     */
    public $events;

    /**
     * @var ArrayCollection|AchievementProgressBar[] $achievements
     * @ORM\OneToMany(targetEntity="App\Entity\AchievementProgressBar", mappedBy="user")
     * @Groups({"GetUser"})
     */
    public $achievements;

    /**
     * @var File $avatar
     * @ORM\OneToOne(targetEntity="App\FileBundle\Entity\File")
     * @Groups({"GetUser", "SetUser"})
     */
    public $avatar;

    /**
     * @var boolean $deleted
     * @Groups({"GetUser"})
     * @ORM\Column(name="deleted", type="boolean", nullable=false)
     */
    protected $deleted = false;

    public function isUser(?UserInterface $user = null): bool
    {
        return $user instanceof self && $user->id === $this->id;
    }

    public function setRolesRaw(array $roles): User
    {
        $this->roles = array();

        foreach ($roles as $role) {
            $this->roles[] = strtoupper($role);
        }
        $this->roles = array_unique($this->roles);
        $this->roles = array_values($this->roles);

        return $this;
    }


    public function addTaskRequest(Task $task): User
    {
        if (!$this->taskRequests->contains($task)) {
            $this->taskRequests[] = $task;
            $task->userRequests->add($this);
        }

        return $this;
    }
    public function removeTaskRequest(Task $task): User
    {
        if ($this->taskRequests->contains($task)){
            $this->taskRequests->removeElement($task);
            $task->userRequests->removeElement($this);
        }

        return $this;
    }

    public function addTask(Task $task): User
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->members->add($this);
        }

        return $this;
    }
    public function removeTask(Task $task): User
    {
        if ($this->tasks->contains($task)) {
            $this->tasks->removeElement($task);
            $task->members->removeElement($this);
        }

        return $this;
    }

    public function addRequestOnChecking(Task $task): User
    {
        if (!$this->requestsOnChecking->contains($task)) {
            $this->requestsOnChecking[] = $task;
            $task->checkingRequests->add($this);
        }

        return $this;
    }
    public function removeRequestOnChecking(Task $task): User
    {
        if ($this->requestsOnChecking->contains($task)) {
            $this->requestsOnChecking->removeElement($task);
            $task->checkingRequests->removeElement($this);
        }

        return $this;
    }

    public function addSuccessfulTask(Task $task): User
    {
        if (!$this->successfulTasks->contains($task)) {
            $this->successfulTasks[] = $task;
            $task->successfulMembers->add($this);
        }

        return $this;
    }
    public function removeSuccessfulTask(Task $task): User
    {
        if ($this->successfulTasks->contains($this)){
            $this->successfulTasks->removeElement($task);
            $task->successfulMembers->removeElement($this);
        }

        return $this;
    }
}
