<?php

namespace App\Entity;

use App\Repository\ProviderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProviderRepository::class)
 */
class Provider
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $domain;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $moviePath;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $seriePath;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $pageQueryString;

    /**
     * @ORM\OneToMany(targetEntity=Scrap::class, mappedBy="provider")
     */
    private $scraps;

    public function __construct()
    {
        $this->scraps = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function getMoviePath(): ?string
    {
        return $this->moviePath;
    }

    public function setMoviePath(?string $moviePath): self
    {
        $this->moviePath = $moviePath;

        return $this;
    }

    public function getSeriePath(): ?string
    {
        return $this->seriePath;
    }

    public function setSeriePath(?string $seriePath): self
    {
        $this->seriePath = $seriePath;

        return $this;
    }

    public function getPageQueryString(): ?string
    {
        return $this->pageQueryString;
    }

    public function setPageQueryString(string $pageQueryString): self
    {
        $this->pageQueryString = $pageQueryString;

        return $this;
    }

    /**
     * @return Collection<int, Scrap>
     */
    public function getScraps(): Collection
    {
        return $this->scraps;
    }

    public function addScrap(Scrap $scrap): self
    {
        if (!$this->scraps->contains($scrap)) {
            $this->scraps[] = $scrap;
            $scrap->setProvider($this);
        }

        return $this;
    }

    public function removeScrap(Scrap $scrap): self
    {
        if ($this->scraps->removeElement($scrap)) {
            // set the owning side to null (unless already changed)
            if ($scrap->getProvider() === $this) {
                $scrap->setProvider(null);
            }
        }

        return $this;
    }
}
