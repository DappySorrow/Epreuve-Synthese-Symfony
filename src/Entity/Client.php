<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\ClientRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[UniqueEntity(fields: ['courriel'], message: 'Il y a déjà un compte avec ce courriel')]
class Client implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idClient')]
    private ?int $idClient = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\Email(message: "Votre adresse courriel {{ value }} est invalide.")]
    private ?string $courriel = null;

    #[ORM\Column(length: 50)]
    #[Assert\Length(min: 2, minMessage: "Le nom doit contenir au minimum {{ limit }} caractères.")]
    #[Assert\Length(max: 30, maxMessage: "Le nom doit contenir au maximum {{ limit }} caractères.")]
    private ?string $nom = null;

    #[ORM\Column(type: "string", length: 50)]
    #[Assert\Length(min: 2, minMessage: "Le prénom doit contenir au minimum {{ limit }} caractères.")]
    #[Assert\Length(max: 30, maxMessage: "Le prénom doit contenir au maximum {{ limit }} caractères.")]
    private ?string $prenom = null;

    #[ORM\Column(name: 'motDePasse', length: 255)]
    #[Assert\Length(min: 6, minMessage: "Le mot de passe doit contenir au minimum {{ limit }} caractères.")]
    private ?string $motDePasse = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 5, minMessage: "L'adresse doit contenir au minimum {{ limit }} caractères.")]
    #[Assert\Length(max: 100, maxMessage: "L'adresse doit contenir au maximum {{ limit }} caractères.")]
    private ?string $adresse = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 2, minMessage: "La ville doit contenir au minimum {{ limit }} caractères.")]
    #[Assert\Length(max: 30, maxMessage: "La ville doit contenir au maximum {{ limit }} caractères.")]
    private ?string $ville = null;

    #[ORM\Column(name: 'codePostal', length: 6)]
    #[Assert\Regex(pattern: "/[AaBbCcEeGgHhJjKkLlMmNnPpRrSsTtVvXxYy][0-9][AaBbCcEeGgHhJjKkLlMmNnPpRrSsTtVvWwXxYyZz][0-9][AaBbCcEeGgHhJjKkLlMmNnPpRrSsTtVvWwXxYyZz][0-9]/", message: "Le code postal '{{ value }}' est invalide.")]
    private ?string $codePostal = null;

    #[ORM\Column(length: 2)]
    private ?string $province = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(pattern: "/[0-9]{3}[0-9]{3}[0-9]{4}/", message: "Votre téléphone doit contenir 10 chiffres")]
    private ?string $telephone = null;

    //Un client a une ou plusieurs commandes
    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Commande::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $commandes;

    public function __construct()
    {
        $this->commandes = new ArrayCollection();
    }

    //Pour symfony
    #[ORM\Column]
    private array $roles = [];

    //Pour symfony
    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    public function getIdClient(): ?int
    {
        return $this->idClient;
    }

    public function getCourriel(): ?string
    {
        return $this->courriel;
    }

    public function setCourriel(string $courriel): self
    {
        $this->courriel = $courriel;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getMotDePasse(): ?string
    {
        return $this->motDePasse;
    }

    public function setMotDePasse(string $motDePasse): self
    {
        $this->motDePasse = $motDePasse;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): self
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getProvince(): ?string
    {
        return $this->province;
    }

    public function setProvince(string $province): self
    {
        $this->province = $province;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getCommandes(Collection $commandes): self
    {
        $this->commandes = $commandes;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->courriel;
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
        return $this->motDePasse;
    }

    public function setPassword(string $motDePasse): self
    {
        $this->motDePasse = $motDePasse;

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

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}
