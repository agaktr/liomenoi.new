<?php

namespace App\Entity;

use App\Repository\MagnetRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MagnetRepository::class)
 */
class Magnet
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
    private $quality;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $size;

    /**
     * @ORM\Column(type="text")
     */
    private $magnet;

    /**
     * @ORM\ManyToOne(targetEntity=movie::class, inversedBy="magnets", fetch="EXTRA_LAZY")
     */
    private $movie;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuality(): ?string
    {
        return $this->quality;
    }

    public function setQuality(string $quality): self
    {
        $this->quality = $quality;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(string $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getMagnet(): ?string
    {
        return $this->magnet;
    }

    public function setMagnet(string $magnet): self
    {
        $this->magnet = $magnet;

        return $this;
    }

    public function getMovie(): ?movie
    {
        return $this->movie;
    }

    public function setMovie(?movie $movie): self
    {
        $this->movie = $movie;

        return $this;
    }
}
