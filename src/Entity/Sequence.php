<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomAssert;

/**
 * Sequence
 *
 * @ORM\Table(name="sequences")
 * @ORM\Entity(repositoryClass="App\Repository\SequenceRepository")
 */
class Sequence
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
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true, options={"default"="NULL"})
     */
    private $description = NULL;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="sequences")
     * @ORM\OrderBy({"name" = "ASC"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     * })
     */
    private $author;

    /**
     * @var int
     *
     * @ORM\Column(name="public", type="integer")
     */
    private $public = 0;

    /**
     * @var \SequencePictogram
     * @ORM\OneToMany(targetEntity="App\Entity\SequencePictogram", mappedBy="sequence")
     * @ORM\OrderBy({"sortNumber" = "ASC"})
    */
    private $sequencePictograms;

    /**
     * @var \Date[]
     * @ORM\OneToMany(targetEntity="App\Entity\Date", mappedBy="sequence")
    */
    private $dates;

    /**
     * Constructor
     */
    public function __construct(User $author)
    {
        $this->pictograms = new ArrayCollection();
        $this->author = $author;
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
    public function getSequencePictograms(): ?Collection
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
        $this->sequencePictograms->removeElement($sequencePictogram);
        return $this;
    }

    public function clearSequencePictograms(): self
    {
        foreach($this->sequencePictograms as $item){
            $item->remove();
        }

        $this->sequencePictograms = [];
        
        return $this;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getPublic(): int
    {
        return $this->public;
    }

    public function setPublic(int $public): self
    {
        if($public) $this->public = 1;
        else $this->public = 0;

        if($this->public==0){
            if(!empty($this->dates)){
                foreach($this->dates as $date){
                    if($this->author->getId()!=$date->getAuthor()->getId())
                        $date->setSequence(null);
                }
            }
        }
        
        return $this;
    }

}
