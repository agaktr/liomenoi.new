<?php

namespace App\Entity;

use App\Entity\Apto\User;
use App\Repository\BoilerplateRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Cache;

/**
 * @ORM\Entity(repositoryClass=BoilerplateRepository::class)
 * @Cache(usage="NONSTRICT_READ_WRITE", region="region")
 */
class Boilerplate
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
    private $Column1;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $Column2;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="boilerplates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getColumn1(): ?string
    {
        return $this->Column1;
    }

    public function setColumn1(string $Column1): self
    {
        $this->Column1 = $Column1;

        return $this;
    }

    public function getColumn2(): ?string
    {
        return $this->Column2;
    }

    public function setColumn2(string $Column2): self
    {
        $this->Column2 = $Column2;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
