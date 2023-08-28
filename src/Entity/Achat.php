<?php

namespace App\Entity;

use App\Repository\AchatRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AchatRepository::class)]
#[ORM\Table(name: 'achats')]
class Achat
{
    public function __construct($produit)
    {
        $this->quantite = 1;
        $this->prixAchat = $produit->getPrix();
        $this->produit = $produit;
    }

    public function majAchat($nouvelleQte)
    {
        $this->quantite = $nouvelleQte;
    }

    public function prixTotal(): ?float
    {
        return $this->quantite * $this->prixAchat;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idAchat')]
    private ?int $idAchat = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\Column(name: 'prixAchat')]
    private ?float $prixAchat = null;

    #[ORM\ManyToOne(cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'idProduit', referencedColumnName: 'idProduit', nullable: false)]
    private ?Produit $produit = null;

    #[ORM\ManyToOne(cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'idCommande', referencedColumnName: 'idCommande', nullable: false)]
    private ?Commande $commande = null;

    //---------------------------------------

    public function getIdAchat(): ?int
    {
        return $this->idAchat;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getPrixAchat(): ?float
    {
        return $this->prixAchat;
    }

    public function setPrixAchat(float $prixAchat): self
    {
        $this->prixAchat = $prixAchat;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(Produit $produit): self
    {
        $this->produit = $produit;

        return $this;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(Commande $commande): ?self
    {
        $this->commande = $commande;

        return $this;
    }
}
