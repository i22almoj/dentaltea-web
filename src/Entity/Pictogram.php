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
/* 

CREATE TABLE `advices` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `title` text DEFAULT NULL,
 `content` text DEFAULT NULL,
 `image` varchar(300) NOT NULL,
 `sort_number` int(11) NOT NULL DEFAULT 0,
 `creation_date` datetime NOT NULL DEFAULT current_timestamp(),
 PRIMARY KEY (`id`)
);

CREATE TABLE `pictograms` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `image` varchar(300) NOT NULL,
 `description` text DEFAULT NULL,
 `creation_date` datetime NOT NULL DEFAULT current_timestamp(),
 PRIMARY KEY (`id`)
);

CREATE TABLE `sequences` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `image` varchar(300) NOT NULL,
 `title` varchar(200) NOT NULL,
 `description` text DEFAULT NULL,
 `author_id` int(11) DEFAULT NULL,
 `creation_date` datetime NOT NULL DEFAULT current_timestamp(),
 PRIMARY KEY (`id`),
 FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE `sequence_pictograms` (
 `sequence_id` int(11) NOT NULL,
 `pictogram_id` int(11) NOT NULL,
 `description` text DEFAULT NULL,
 `sort_number` int(11) NOT NULL DEFAULT 0,
 PRIMARY KEY (`sequence_id`, `pictogram_id`),
 FOREIGN KEY (`sequence_id`) REFERENCES `sequences`(`id`) ON DELETE CASCADE,
 FOREIGN KEY (`pictogram_id`) REFERENCES `pictograms`(`id`) ON DELETE CASCADE
);

CREATE TABLE `dates` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `date_date` datetime NOT NULL,
 `description` text DEFAULT NULL,
 `author_id` int(11) DEFAULT NULL,
 `creation_date` datetime NOT NULL DEFAULT current_timestamp(),
 PRIMARY KEY (`id`),
 FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE `date_sequences` (
 `date_id` int(11) NOT NULL,
 `sequence_id` int(11) NOT NULL,
 `sort_number` int(11) NOT NULL DEFAULT 0,
 PRIMARY KEY (`date_id`, `sequence_id`),
 FOREIGN KEY (`date_id`) REFERENCES `dates`(`id`) ON DELETE CASCADE,
 FOREIGN KEY (`sequence_id`) REFERENCES `sequences`(`id`) ON DELETE CASCADE
);
*/