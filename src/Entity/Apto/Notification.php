<?php

namespace App\Entity\Apto;

use App\Repository\Apto\NotificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;

/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 * @Cache(usage="NONSTRICT_READ_WRITE", region="region")
 */
class Notification
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
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="notifications")
     * @Cache(usage="NONSTRICT_READ_WRITE", region="region")
     */
    private $users;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="seenNotifications")
     * @JoinTable(name="notification_seen")
     *      joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="notification_id", referencedColumnName="id")}
     *      )
     * @Cache(usage="NONSTRICT_READ_WRITE", region="region")
     */
    private $seen;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->seen = new ArrayCollection();
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getSeen(): Collection
    {
        return $this->seen;
    }

    public function addSeen(User $seen): self
    {
        if (!$this->seen->contains($seen)) {
            $this->seen[] = $seen;
        }

        return $this;
    }

    public function removeSeen(User $seen): self
    {
        $this->seen->removeElement($seen);

        return $this;
    }
}
