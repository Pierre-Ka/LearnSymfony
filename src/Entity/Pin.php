<?php

namespace App\Entity;

use App\Entity\Traits\Timestampable;
use App\Repository\PinRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/* *********** VICH *****************/
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
/**************************************/
/*
    Symfony\Component\Validator\Constraints as Assert; permet de mettre des contraintes de validation
 */

#[ORM\Entity(repositoryClass: PinRepository::class)]
#[ORM\HasLifecycleCallbacks]
/* *********** VICH *****************/
/*
    La notation #[Vich\Uploadable] entraine l'erreur : The class "App\Entity\Pin" is not uploadable
 */
/**
 * @Vich\Uploadable
 */
/**************************************/
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

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $imageName;

    /* *********** VICH *****************/
    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="pin_image", fileNameProperty="imageName")
     *
     * @var File|null
     */

    #[Assert\Image(maxSize:"10M", maxSizeMessage:'Le fichier doit être inferieur à 10Mo !')]    /* Ajout */
    /*
        #[Assert\NotNull(message:'Please upload a file')]
        Cela equivaut à rendre l'image obligatoire. La contrainte peut être définie au niveau du form aussi.
    */

    private $imageFile;
    /*
        Il nous faut personnalisé les annotations: on a la ligne :
        @Vich\UploadableField(mapping="product_image", fileNameProperty="imageName", size="imageSize")
        mapping est le nom de notre mapping ( dans vich_uploader.yalm, fileNameProperty est le nom de la colonne
        contenant le nom de l'image ( ici, la propriété précédente : private $imageName;, size est le nom de la
        colonne contenant la taille de l'image que l'on ne va pas créer pour le coup.
     */
    /****************************************/

    /*
        Dans ce projet, on a rajouter 2 champs ( s make:entity Pin ) : createdAt et updatedAt que l'on
        a mis dans un trait : Timestampable. On a défini ces deux colonnes comme nullables.
        Une methode non recommandée consisterait à définir ces colonnes comme non nullables puis à définir
        une valeur par défault avant la migration avec :
        #[ORM\Column(type: 'datetime_immutable', options : "default"="CURRENT_TIMESTAMP") )]
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

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }

    /* *********** VICH *****************/
    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->setUpdatedAtValue();
        }
    }
    /*
        Le setter setImageFile DOIT avoir le ? devant File. A l'interieur du setter, on va changer le code
        $this->updatedAt = new \DateTimeImmutable(); par
        $this->setUpdatedAt(new \DateTimeImmutable);
        car on possède deja notre setter setUpdatedAt() dans notre trait Timestampable
    */
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }
    /**************************************************/
}
