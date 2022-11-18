<?php

namespace App\Entity;

use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MovieRepository::class)
 */
class Movie
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
    private $title;

    /**
     * @ORM\Column(type="integer")
     */
    private $year;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $imdb;

    /**
     * @ORM\OneToMany(targetEntity=Magnet::class, mappedBy="movie")
     */
    private $magnets;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $slug;

    public function __construct()
    {
        $this->magnets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getImdb(): ?string
    {
        return $this->imdb;
    }

    public function setImdb(string $imdb): self
    {
        $this->imdb = $imdb;

        return $this;
    }

    /**
     * @return Collection<int, Magnet>
     */
    public function getMagnets(): Collection
    {
        return $this->magnets;
    }

    public function addMagnet(Magnet $magnet): self
    {
        if (!$this->magnets->contains($magnet)) {
            $this->magnets[] = $magnet;
            $magnet->setMovie($this);
        }

        return $this;
    }

    public function removeMagnet(Magnet $magnet): self
    {
        if ($this->magnets->removeElement($magnet)) {
            // set the owning side to null (unless already changed)
            if ($magnet->getMovie() === $this) {
                $magnet->setMovie(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}
