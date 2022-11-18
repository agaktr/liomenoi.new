<?php

namespace App\Entity\Apto;

use App\Entity\Boilerplate;
use App\Repository\Apto\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Cache;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 * @Cache(usage="NONSTRICT_READ_WRITE", region="region")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\ManyToMany(targetEntity=Notification::class, mappedBy="users")
     * @Cache(usage="NONSTRICT_READ_WRITE", region="region")
     */
    private $notifications;

    /**
     * @ORM\ManyToMany(targetEntity=Notification::class, mappedBy="seen")
     * @Cache(usage="NONSTRICT_READ_WRITE", region="region")
     */
    private $seenNotifications;

    /**
     * @ORM\OneToMany(targetEntity=Boilerplate::class, mappedBy="user")
     * @Cache(usage="NONSTRICT_READ_WRITE", region="region")
     */
    private $boilerplates;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated;

    public function __construct()
    {
        $this->notifications = new ArrayCollection();
        $this->seenNotifications = new ArrayCollection();
        $this->boilerplates = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->username;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function setPlainPassword($plainPassword): string
    {
        return $this;
    }

    public function getPlainPassword(): string
    {
        return '';
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->addUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            $notification->removeUser($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getSeenNotifications(): Collection
    {
        return $this->seenNotifications;
    }

    public function addSeenNotification(Notification $seenNotification): self
    {
        if (!$this->seenNotifications->contains($seenNotification)) {
            $this->seenNotifications[] = $seenNotification;
            $seenNotification->addSeen($this);
        }

        return $this;
    }

    public function removeSeenNotification(Notification $seenNotification): self
    {
        if ($this->seenNotifications->removeElement($seenNotification)) {
            $seenNotification->removeSeen($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Boilerplate>
     */
    public function getBoilerplates(): Collection
    {
        return $this->boilerplates;
    }

    public function addBoilerplate(Boilerplate $boilerplate): self
    {
        if (!$this->boilerplates->contains($boilerplate)) {
            $this->boilerplates[] = $boilerplate;
            $boilerplate->setUser($this);
        }

        return $this;
    }

    public function removeBoilerplate(Boilerplate $boilerplate): self
    {
        if ($this->boilerplates->removeElement($boilerplate)) {
            // set the owning side to null (unless already changed)
            if ($boilerplate->getUser() === $this) {
                $boilerplate->setUser(null);
            }
        }

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }
}
