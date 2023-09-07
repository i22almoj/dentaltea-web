<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomAssert;

/**
 * Advice
 *
 * @ORM\Table(name="advices")
 * @ORM\Entity(repositoryClass="App\Repository\AdviceRepository")
 */
class Advice
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
     * @var \DateTime
     *
     * @ORM\Column(name="creation_time", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $creationTime = 'current_timestamp()';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=200, nullable=false)
	 * @Assert\NotBlank
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(name="content", type="text", length=65535, nullable=true, options={"default"="NULL"})
     */
    private $content = NULL;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=300, nullable=true, options={"default"="NULL"})
     */
    private $image = NULL;

    /**
     * @var int
     *
     * @ORM\Column(name="sort_number", type="integer", nullable=false, options={"default" : 0})
     */
    private $sortNumber = 0;

 
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pictograms = new ArrayCollection();
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

    public function getCreationTime(): ?\DateTimeInterface
    {
        return $this->creationTime;
    }

    public function setCreationTime(\DateTimeInterface $creationTime): self
    {
        $this->creationTime = $creationTime;

        return $this;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage($image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getSortNumber(): ?int
    {
        return $this->sortNumber;
    }

    public function setSortNumber(int $sortNumber): self
    {
        $this->sortNumber = $sortNumber;

        return $this;
    }

}
