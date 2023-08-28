<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[UniqueEntity('nom', message: 'Un produit détient déjà ce nom')]
#[ORM\Table(name: 'produits')]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idProduit')]
    private ?int $idProduit = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotNull(message: "Le nom ne peut être vide")]
    private ?string $nom = null;

    #[ORM\Column(name: 'prix')]
    #[Assert\NotNull(message: "Le prix ne peut être vide")]
    #[Assert\Type(type: 'float', message: "Le prix doit être un nombre")]
    private ?float $prix = null;

    #[ORM\Column(name: 'quantiteEnStock')]
    #[Assert\NotNull(message: "La quantité en stock ne peut être vide")]
    private ?int $quantiteEnStock = null;

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'imagePath', length: 255, nullable: true)]
    private ?string $imagePath = null;

    //-------------------------------------------------------

    #[ORM\ManyToOne(targetEntity: Categorie::class, inversedBy: "produits", cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(name: 'categorie', referencedColumnName: 'idCategorie')]
    #[Assert\NotNull(message: "La catégorie ne peut être vide")]
    private $categorie;

    //-------------------------------------------------------

    public function getIdProduit(): ?int
    {
        return $this->idProduit;
    }

    //-------

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    //-------

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    //-------

    public function getQuantiteEnStock(): ?int
    {
        return $this->quantiteEnStock;
    }

    public function setQuantiteEnStock(int $quantiteEnStock): self
    {
        $this->quantiteEnStock = $quantiteEnStock;

        return $this;
    }

    //-------

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    //-------

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): self
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    //-------

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(Categorie $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }
}
