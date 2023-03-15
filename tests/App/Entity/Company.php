<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests\App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use whatwedo\SearchBundle\Annotation\Index;

#[ORM\Table(name: 'company')]
#[ORM\Entity(repositoryClass: 'whatwedo\SearchBundle\Tests\App\Repository\CompanyRepository')]
class Company
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[Index(groups: ['default', 'company', 'global'])]
    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    private ?string $name = null;

    #[Index(formatter: 'whatwedo\SearchBundle\Tests\App\Formatter\DummyFormatter')]
    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    private ?string $city = null;

    #[Index]
    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    private ?string $country = null;

    #[Index]
    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    private ?string $taxIdentificationNumber = null;

    /**
     * @var Collection|array<Contact> One Member has Many Departments
     */
    #[ORM\OneToMany(targetEntity: 'Contact', mappedBy: 'company')]
    private $contacts;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTaxIdentificationNumber(): ?string
    {
        return $this->taxIdentificationNumber;
    }

    public function setTaxIdentificationNumber(?string $taxIdentificationNumber): void
    {
        $this->taxIdentificationNumber = $taxIdentificationNumber;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addDepartment(Contact $department): self
    {
        if (! $this->contacts->contains($department)) {
            $this->contacts[] = $department;
        }

        return $this;
    }

    public function removeDepartment(Contact $department): self
    {
        if ($this->contacts->contains($department)) {
            $this->contacts->removeElement($department);
        }

        return $this;
    }
}
