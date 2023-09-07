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
 * Sequence
 *
 * @ORM\Table(name="sequence_pictograms")
 * @ORM\Entity
 */
class SequencePictogram
{
    /**
     * @var \Sequence
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Entity\Sequence", inversedBy="sequencePictograms")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sequence_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $sequence;

    /**
     * @var \Pictogram
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Entity\Pictogram", inversedBy="sequencePictograms")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pictogram_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $pictogram;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true, options={"default"="NULL"})
     */
    private $description = NULL;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="sort_number", type="integer", nullable=false, options={"default" : 0})
     */
    private $sortNumber;

 
    /**
     * Constructor
     */
    public function __construct()
    {
        
    }

    public function getSequence(): ?Sequence
    {
        return $this->sequence;
    }

    public function setSequence(Sequence $sequence): self
    {
        $this->sequence = $sequence;

        return $this;
    }

    public function getPictogram(): ?Pictogram
    {
        return $this->pictogram;
    }

    public function setPictogram(Pictogram $pictogram): self
    {
        $this->pictogram = $pictogram;

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