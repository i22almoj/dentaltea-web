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
 * Date
 *
 * @ORM\Table(name="dates")
 * @ORM\Entity(repositoryClass="App\Repository\DateRepository")
 */
class Date
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
     * @var \DateTime
     *
     * @ORM\Column(name="date_time", type="datetime", nullable=false)
     */
    private $dateTime;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true, options={"default"="NULL"})
     */
    private $description = NULL;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="dates")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     * })
     */
    private $author;

    /**
     * @var \Sequence
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Sequence", inversedBy="dates")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sequence_id", referencedColumnName="id")
     * })
     */
    private $sequence;

    /**
     * @var \Sequence
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Sequence", inversedBy="dates")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sequence_id", referencedColumnName="id")
     * })
     */
    
    /**
     * @var int
     *
     * @ORM\Column(name="notifications_mobile", type="integer")
     */
    private $notificationsMobile = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="notifications_Email", type="integer")
     */
    private $notificationsEmail = 0;


    /**
     * Constructor
     */
    public function __construct(User $author)
    {
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


    public function getDateTime(): ?\DateTimeInterface
    {
        return $this->dateTime;
    }

    public function setDateTime(\DateTimeInterface $dateTime): self
    {
        $this->dateTime = $dateTime;

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

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getSequence(): ?Sequence
    {
        return $this->sequence;
    }

    public function setSequence(?Sequence $sequence): self
    {
        $this->sequence = $sequence;

        return $this;
    }

    public function getNotificationsMobile(): int
    {
        return $this->notificationsMobile;
    }

    public function setNotificationsMobile(int $notificationsMobile): self
    {
        if($notificationsMobile) $this->notificationsMobile = 1;
        else $this->notificationsMobile = 0;

        return $this;
    }

    public function getNotificationsEmail(): int
    {
        return $this->notificationsEmail;
    }

    public function setNotificationsEmail(int $notificationsEmail): self
    {
        if($notificationsEmail) $this->notificationsEmail = 1;
        else $this->notificationsEmail = 0;

        return $this;
    }    

}
