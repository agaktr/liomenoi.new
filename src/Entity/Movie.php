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

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $poster;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $backdrop;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $originalTitle;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $overview;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $releaseDate;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $runtime;

    /**
     * @ORM\ManyToMany(targetEntity=Category::class, inversedBy="movies")
     */
    private $categories;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tmdbId;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $fetched;

    /**
     * @ORM\ManyToMany(targetEntity=Actor::class, mappedBy="movies")
     */
    private $actors;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $matchName;

    /**
     * @ORM\OneToMany(targetEntity=Scrap::class, mappedBy="movie")
     */
    private $scraps;

    public function __construct()
    {
        $this->magnets = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->actors = new ArrayCollection();
        $this->scraps = new ArrayCollection();
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

    public function getPoster(): ?string
    {
        return $this->poster;
    }

    public function setPoster(?string $poster): self
    {
        $this->poster = $poster;

        return $this;
    }

    public function getBackdrop(): ?string
    {
        return $this->backdrop;
    }

    public function setBackdrop(?string $backdrop): self
    {
        $this->backdrop = $backdrop;

        return $this;
    }

    public function getOriginalTitle(): ?string
    {
        return $this->originalTitle;
    }

    public function setOriginalTitle(?string $originalTitle): self
    {
        $this->originalTitle = $originalTitle;

        return $this;
    }

    public function getOverview(): ?string
    {
        return $this->overview;
    }

    public function setOverview(?string $overview): self
    {
        $this->overview = $overview;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(?\DateTimeInterface $releaseDate): self
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getRuntime(): ?string
    {
        return $this->runtime;
    }

    public function setRuntime(?string $runtime): self
    {
        $this->runtime = $runtime;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function getTmdbId(): ?int
    {
        return $this->tmdbId;
    }

    public function setTmdbId(?int $tmdbId): self
    {
        $this->tmdbId = $tmdbId;

        return $this;
    }

    public function isFetched(): ?bool
    {
        return $this->fetched;
    }

    public function setFetched(?bool $fetched): self
    {
        $this->fetched = $fetched;

        return $this;
    }

    /**
     * @return Collection<int, Actor>
     */
    public function getActors(): Collection
    {
        return $this->actors;
    }

    public function addActor(Actor $actor): self
    {
        if (!$this->actors->contains($actor)) {
            $this->actors[] = $actor;
            $actor->addMovie($this);
        }

        return $this;
    }

    public function removeActor(Actor $actor): self
    {
        if ($this->actors->removeElement($actor)) {
            $actor->removeMovie($this);
        }

        return $this;
    }

    public function getMatchName(): ?string
    {
        return $this->matchName;
    }

    public function setMatchName(string $matchName): self
    {
        $this->matchName = $matchName;

        return $this;
    }

    /**
     * @return Collection<int, Scrap>
     */
//    public function getScraps(): Collection
//    {
//        return $this->scraps;
//    }

    public function addScrap(Scrap $scrap): self
    {
        if (!$this->scraps->contains($scrap)) {
            $this->scraps[] = $scrap;
            $scrap->setMovie($this);
        }

        return $this;
    }

    public function removeScrap(Scrap $scrap): self
    {
        if ($this->scraps->removeElement($scrap)) {
            // set the owning side to null (unless already changed)
            if ($scrap->getMovie() === $this) {
                $scrap->setMovie(null);
            }
        }

        return $this;
    }
}
