<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests\Fixtures\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="department")
 * @ORM\Entity(repositoryClass="whatwedo\SearchBundle\Tests\Fixtures\Repository\DepartmentRepository")
 */
class Department implements \Stringable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     * @Assert\NotBlank
     * @Assert\NotNull()
     */
    private ?string $name = null;

    /**
     * @ORM\Column(type="integer")
     */
    private int $sortorder = 0;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private ?string $color = null;

    /**
     * Many Groups have Many Members.
     *
     * @var Collection|array<Event>
     * @ORM\ManyToMany(targetEntity="whatwedo\SearchBundle\Tests\Fixtures\Entity\Event", mappedBy="departments")
     */
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setColor($color): void
    {
        $this->color = $color;
    }

    /**
     * @return: Collection
     */
    public function getEvents(): Collection
    {
        /** @var ArrayCollection $events */
        $events = $this->events->filter(
            static function (Event $event) {
                return $event->getStartDate() > new \DateTime('now');
            }
        );

        return $events; //->matching($criteria);
    }

    public function getSortorder()
    {
        return $this->sortorder;
    }

    public function setSortorder($sortorder): void
    {
        $this->sortorder = $sortorder;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
