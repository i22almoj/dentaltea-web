<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomAssert;

/**
 * User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, nullable=false)
	 * @Assert\NotBlank
     * @CustomAssert\UniqueEmail(mode="loose")
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=50, nullable=false)
	 * @Assert\NotBlank
     * @Assert\Length(
     *      min = 6,
     *      max = 50,
     *      minMessage = "La contraseña debe tener al menos {{ limit }} caracteres",
     *      maxMessage = "La contraseña debe tener como máximo {{ limit }} caracteres"
     * )
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
	 * @Assert\NotBlank
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string", length=25, nullable=false, options={"default"="ROLE_USER"})
	 * @Assert\NotBlank
     * @Assert\Choice({"ROLE_USER", "ROLE_ADMIN"})
     */
    private $role = 'ROLE_USER';

   
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_time", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $creationTime = 'current_timestamp()';

    /**
     * @var int
     *
     * @ORM\Column(name="active", type="integer")
     */
    private $active = 1;

    /**
     * @var \Sequence
     * @ORM\OneToMany(targetEntity="App\Entity\Sequence", mappedBy="author")
    */
    private $sequences;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->info = new \Doctrine\Common\Collections\ArrayCollection();
        $this->sequences = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->email;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getCreationTime(): ?\DateTimeInterface
    {
        return $this->creationTime;
    }

    public function setCreationTime(\DateTimeInterface $creationTime): self
    {
        $this->creationTime = $creationTime;

        return $this;
    }

    public function getActive(): int
    {
        return $this->active;
    }

    public function setActive(int $active): self
    {
        if($active) $this->active = 1;
        else $this->active = 0;

        return $this;
    }

    /**
     * @return Collection|Sequence[]
    */
    public function getSequences(): Collection
    {
        return $this->sequences;
    }

    public function addSequencePictogram(Sequence $sequence): self
    {
        if (!$this->sequences->contains($sequence)) {
            $this->sequences[] = $sequence;
        }
        return $this;
    }

    public function removeSequence(Sequence $sequence): self
    {
        $this->sequences->removeElement($sequence);
        return $this;
    }

    public function getSalt(): ?string
    {
		return null;
	}
	
	public function getRoles(): array
          {
      		return array($this->getRole());
      	}
	
	public function eraseCredentials(){}

}
