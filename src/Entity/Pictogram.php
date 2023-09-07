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
 * Pictogram
 *
 * @ORM\Table(name="pictograms")
 * @ORM\Entity(repositoryClass="App\Repository\PictogramRepository")

 */
class Pictogram
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
     * @ORM\Column(name="image", type="string", length=300, nullable=false)
     */
    private $image;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true, options={"default"="NULL"})
     * 
     */
    private $description = NULL;
    
    /**
     * @var \SequencePictogram
     * @ORM\OneToMany(targetEntity="App\Entity\SequencePictogram", mappedBy="pictogram")
    */
    private $sequencePictograms;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_time", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $creationTime = 'current_timestamp()';

    /**
     * Constructor
     */
    public function __construct()
    {
        
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|SequencePictogram[]
    */
    public function getSequencePictograms(): Collection
    {
        return $this->sequencePictograms;
    }

    public function addSequencePictogram(SequencePictogram $sequencePictogram): self
    {
        if (!$this->sequencePictograms->contains($sequencePictogram)) {
            $this->sequencePictograms[] = $sequencePictogram;
        }
        return $this;
    }

    public function removeSequencePictogram(SequencePictogram $sequencePictogram): self
    {
        $this->sequencePictogram->removeElement($sequencePictogram);
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

}