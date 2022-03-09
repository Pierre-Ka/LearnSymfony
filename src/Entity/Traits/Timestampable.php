<?php

namespace App\Entity\Traits;

use DateTimeImmutable;
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
    #[ORM\Column(type: 'datetime_immutable')]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function getCreatedAt(): ?DateTimeImmutable
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
        $this->createdAt = new DateTimeImmutable();
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
