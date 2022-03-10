<?php

namespace App\Entity\Traits;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/*
    Pour pouvoir utiliser ce trait :
    Specifier : use Timestampable; dans la classe voulue
    Declarer le namespace : use App\Entity\Traits\Timestampable;
    Declarer #[ORM\HasLifecycleCallbacks] avant la classe

    #[ORM\HasLifecycleCallbacks] permet de dire que l'on va appeler certaines classes spéciales
    comme #[ORM\PrePersist] ou #[ORM\PreUpdate] ( il y en a plein d'autres ) à l'interieur de la classe.
    Ici on voit que ces fonctions sont appelées à l'interieur du trait lui-même à l'interieur de la classe.
 */
trait Timestampable
{
    #[ORM\Column(type: 'datetime')]
    private ?DateTime $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $updatedAt = null;

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    /*
        #[ORM\PrePersist] : cette fonction sera appelée avant chaque persist
        ( de l'objet de la classe dans laquelle est implementer ce trait )
        De même pour #[ORM\PreUpdate] mais à chaque update.
     */
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
