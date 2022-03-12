<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Traits\Timestampable;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $firstName;

    #[ORM\Column(type: 'string', length: 255)]
    private $lastName;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $email;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    private $password;

    /*
        La propriété $pins est la reciproque de la propriété $user dans la classe Pin
        Si elle permet de recuperer les objets $pins sous forme de tableau, elle n'est en revanche
        pas présente dans la table pin ( remarquer l'absence de #[ORM\Column(type: '')].
        En effet la propriété $user dans la classe Pin est suffisante ( cad la colonne user_id en tant que
        clé étrangère dans la table pin ) pour lier les 2 tables

        Le pin ne peut pas être orphelin cad : lorsque l'user est détruit TOUS les pins qui lui sont associés sont
        détruit. Mais egalement même si l'user n'est pas supprimé il suffit de définir $pin->setUser à null pour
        détruire le pin : en effet il ne peut pas être orphelin !
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Pin::class, orphanRemoval: true)]
    private $pins;

    public function __construct()
    {
        /*
            Lors de la mise en relation entre la table pin et user avec le champ pin.user, on a définit
            que l'on voulait une valeur reciproque dans la table User qui nous permet de recuperer tous ses pins :
            $user->getPins(); ( car relation de type ManyToOne ). Pour cela on doit avoir une collection de Pins
            pour avoir tous les pins associées à l'utilisateur. Au niveau de Doctrine on utilise des :
            Doctrine\Common\Collections\ArrayCollection;. Il s'agit d'un tableau doté de methodes.
            Donc $pins sera un objet de type ArrayCollection.
         */
        $this->pins = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /*
        $pins est un objet de type ArrayCollection.
        $user->getPins() lui nous retourne une Collection de $pins
        Exemple : count($u2->getPins())
     */
    /**
     * @return Collection<int, Pin>
     */
    public function getPins(): Collection
    {
        return $this->pins;
    }

    /*
        $pins est un objet de type ArrayCollection.
        On peut rajouter un $pin à cette collection en utilisant $user->addPin($newPin)
        On peut supprimer un $pin à cette collection en utilisant $user->removePin($newPin)
        La classe ArrayCollection de Doctrine possède plusieurs methodes dont contains() :
        est-ce que le $pin est présent dans la collection ou encore removeElement().
        $pin->setUser($this); Explication : on prend notre pin et on lui passe en auteur : $this :
        c'est à dire qu'on lui passe en auteur notre objet $user actuel.
        Exemple : $u1->addPin($pin2);
     */
    public function addPin(Pin $pin): self
    {
        if (!$this->pins->contains($pin)) {
            $this->pins[] = $pin;
            $pin->setUser($this);
        }

        return $this;
    }

    public function removePin(Pin $pin): self
    {
        if ($this->pins->removeElement($pin)) {
            // set the owning side to null (unless already changed)
            if ($pin->getUser() === $this) {
                $pin->setUser(null);
            }
        }

        return $this;
    }

    public function getFullName() : string
    {
        return $this->getFirstName(). ' ' .$this->getLastName();
    }
}
