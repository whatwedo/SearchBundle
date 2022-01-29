<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests\Fixtures\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="event")
 * @ORM\Entity(repositoryClass="whatwedo\SearchBundle\Tests\Fixtures\Repository\EventRepository")
 */
class Event
{
    public const TYPE_MEETING = 'meeting';

    public const TYPE_APOINTMENT = 'apointment';

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @Assert\NotNull()
     */
    private ?string $name = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $location = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private bool $videoConference = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     * @Assert\NotNull
     */
    private ?\DateTimeInterface $startDate = null;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     * @Assert\NotNull
     * @Assert\GreaterThanOrEqual(
     *     propertyPath = "startDate"
     * )
     */
    private ?\DateTimeInterface $endDate = null;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Assert\LessThan(
     *     propertyPath = "startDate"
     * )
     */
    private ?\DateTimeInterface $deadlineDate = null;

    /**
     * @ORM\Column(name="`allDay`", type="boolean")
     */
    private bool $allDay = false;

    /**
     * @ORM\Column(name="`public`", type="boolean")
     */
    private bool $public = false;

    /**
     * @ORM\Column(name="`restricted`", type="boolean")
     */
    private bool $restricted = true;

    /**
     * @var Collection|array<Department> One Member has Many Departments
     * @ORM\ManyToMany(targetEntity="whatwedo\SearchBundle\Tests\Fixtures\Entity\Department", inversedBy="events")
     * @ORM\JoinTable(name="`event_department`")
     */
    private $departments;

    /**
     * @var Collection|array<self>
     * @ORM\OneToMany(targetEntity="whatwedo\SearchBundle\Tests\Fixtures\Entity\Event", mappedBy="parent")
     * @ORM\OrderBy ({"startDate" = "ASC"})
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="whatwedo\SearchBundle\Tests\Fixtures\Entity\Event", inversedBy="children")
     * @ORM\JoinColumn(name="`parent_id`", referencedColumnName="id")
     */
    private ?Event $parent = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->departments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isVideoConference(): bool
    {
        return $this->videoConference;
    }

    public function setVideoConference(bool $videoConference): self
    {
        $this->videoConference = $videoConference;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function isAllDay(): bool
    {
        return $this->allDay;
    }

    public function setAllDay(bool $allDay): self
    {
        $this->allDay = $allDay;

        return $this;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|array<self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $event): self
    {
        if (! $this->children->contains($event)) {
            $event->setParent($this);
            $this->children[] = $event;
        }

        return $this;
    }

    public function removeChild(self $event): self
    {
        if ($this->children->contains($event)) {
            $this->children->removeElement($event);
            $event->setParent(null);
        }

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getDepartments(): Collection
    {
        return $this->departments;
    }

    public function addDepartment(Department $department): self
    {
        if (! $this->departments->contains($department)) {
            $this->departments[] = $department;
        }

        return $this;
    }

    public function removeDepartment(Department $department): self
    {
        if ($this->departments->contains($department)) {
            $this->departments->removeElement($department);
        }

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): self
    {
        $this->public = $public;

        return $this;
    }

    public function isRestricted(): bool
    {
        return $this->restricted;
    }

    public function setRestricted(bool $restricted): self
    {
        $this->restricted = $restricted;

        return $this;
    }

    public function getDeadlineDate(): ?\DateTimeInterface
    {
        return $this->deadlineDate;
    }

    public function setDeadlineDate(?\DateTimeInterface $deadlineDate): self
    {
        $this->deadlineDate = $deadlineDate;

        return $this;
    }
}
