<?php

namespace App\Entity;

use App\Entity\Traits\Timestampable;
use App\Repository\PinRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/*
    Symfony\Component\Validator\Constraints as Assert; permet de mettre des contraintes de validation
 */

#[ORM\Entity(repositoryClass: PinRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Pin
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Title can\'t be blank')]
    /*
        Ici il s'agit de la validation coté serveur : un champ vide ne sera pas accepté.
        Malgré cette contrainte on a une erreur :

        ERROR : Expected argument of type "string", "null" given at property path "title".

        En fait il faut changer notre setter pour lui permettre d'avoir des champs vides afin de laisser
        la validation par Assert s'effectuer :
        public function setTitle(string $title): self => public function setTitle(?string $title): self
        Pareil pour description.
        Ici on a mis un message qui s'affiche en cas de non-respect ; en vrai il existe un message par default
    */
    #[Assert\Length(min: 3)]
    private $title;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Description can\'t be blank')]
    #[Assert\Length(min: 10, minMessage: 'Description is too short!')]
    private $description;

    /*
        Dans ce projet, on a rajouter 2 champs ( s make:entity Pin ) : createdAt et updatedAt que l'on
        a mis dans un trait : Timestampable. On a défini ces deux colonnes comme nullables.
        Une methode non recommandée consisterait à définir ces colonnes comme non nullables puis à définir
        une valeur par défault avant la migration avec :
        #[ORM\Column(type: 'datetime_immutable', options : "default"="CURRENT_TIMESTAMP") )]
        ou ( oubli de ma part )
        #[ORM\Column(type: 'datetime_immutable', options : "default":"CURRENT_TIMESTAMP") )]
    */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
