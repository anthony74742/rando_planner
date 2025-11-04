<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, Hike>
     */
    #[ORM\OneToMany(targetEntity: Hike::class, mappedBy: 'creator')]
    private Collection $hikesCreated;

    /**
     * @var Collection<int, HikeSession>
     */
    #[ORM\OneToMany(targetEntity: HikeSession::class, mappedBy: 'creator')]
    private Collection $hikeSessions;

    public function __construct()
    {
        $this->hikesCreated = new ArrayCollection();
        $this->hikeSessions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
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

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    /**
     * @return Collection<int, Hike>
     */
    public function getCreator(): Collection
    {
        return $this->hikesCreated;
    }

    public function addCreator(Hike $hikesCreated): static
    {
        if (!$this->hikesCreated->contains($hikesCreated)) {
            $this->hikesCreated->add($hikesCreated);
            $hikesCreated->setCreator($this);
        }

        return $this;
    }

    public function removeCreator(Hike $hikesCreated): static
    {
        if ($this->hikesCreated->removeElement($hikesCreated)) {
            // set the owning side to null (unless already changed)
            if ($hikesCreated->getCreator() === $this) {
                $hikesCreated->setCreator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HikeSession>
     */
    public function getHikeSessions(): Collection
    {
        return $this->hikeSessions;
    }

    public function addHikeSession(HikeSession $hikeSession): static
    {
        if (!$this->hikeSessions->contains($hikeSession)) {
            $this->hikeSessions->add($hikeSession);
            $hikeSession->setCreator($this);
        }

        return $this;
    }

    public function removeHikeSession(HikeSession $hikeSession): static
    {
        if ($this->hikeSessions->removeElement($hikeSession)) {
            // set the owning side to null (unless already changed)
            if ($hikeSession->getCreator() === $this) {
                $hikeSession->setCreator(null);
            }
        }

        return $this;
    }
}
