<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Achat;
use App\Entity\Client;
use App\Entity\Constantes;

use App\Repository\CommandeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
#[ORM\Table(name: 'commandes')]
class Commande
{
    public function __construct($user, $panier, $paymentIntent)
    {
        $this->dateCommande = new DateTime((date("Y-m-d H:i:s", time())));
        $this->dateLivraison = null;
        $this->tauxTPS = Constantes::TPS;
        $this->tauxTVQ = Constantes::TVQ;
        $this->fraisLivraison = Constantes::FRAIS_LIVRAISON;
        $this->etat = Etat::PREPARATION;
        $this->client = $user;
        $this->stripeIntent = $paymentIntent;

        $this->achats = new ArrayCollection();

        foreach ($panier->getAchats() as $achat) {
            $this->achats->add($achat);
            $achat->setCommande($this);
        }
    }

    public function calculerSousTotal()
    {
        $sousTotal = 0;

        foreach ($this->achats as $achat) {
            $sousTotal += ($achat->prixTotal());
        }

        return $sousTotal;
    }


    public function calculerPrixTotal()
    {
        $prixTotal = 0;

        foreach ($this->achats as $achat) {
            $prixTotal += ($achat->prixTotal());
        }

        $tps = $prixTotal * $this->tauxTPS / 100;
        $tvq = $prixTotal * $this->tauxTVQ / 100;

        return $prixTotal + $this->fraisLivraison +  $tps +  $tvq;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idCommande')]
    private ?int $idCommande = null;

    #[ORM\Column(name: 'dateCommande', nullable: false)]
    private ?DateTime $dateCommande = null;

    #[ORM\Column(name: 'dateLivraison', nullable: true)]
    private ?DateTime $dateLivraison = null;

    #[ORM\Column(name: 'tauxTPS')]
    private ?float $tauxTPS = null;

    #[ORM\Column(name: 'tauxTVQ')]
    private ?float $tauxTVQ = null;

    #[ORM\Column(name: 'fraisLivraison')]
    private ?float $fraisLivraison = null;

    #[ORM\Column(length: 255)]
    private ?string $etat = null;

    #[ORM\Column(name: 'stripeIntent', length: 255)]
    private ?string $stripeIntent = null;

    //Une commande a une ou plusieurs achats
    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: Achat::class, cascade: ['persist'])]
    private Collection $achats;

    //Plusieurs commandes ont un client
    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: "commandes", cascade: ["persist"])]
    #[ORM\JoinColumn(name: 'idClient', referencedColumnName: 'idClient', nullable: false)]
    private ?Client $client;

    //---------------------------------------

    public function getIdCommande(): ?int
    {
        return $this->idCommande;
    }

    public function getDateCommande(): ?DateTime
    {
        return $this->dateCommande;
    }

    public function getDateLivraison(): ?DateTime
    {
        return $this->dateLivraison;
    }

    public function setDateLivraison(?DateTime $dateLivraison): self
    {
        $this->dateLivraison = $dateLivraison;

        return $this;
    }

    public function getTauxTPS(): ?float
    {
        return $this->tauxTPS;
    }

    public function getTauxTVQ(): ?float
    {
        return $this->tauxTVQ;
    }

    public function getFraisLivraison(): ?float
    {
        return $this->fraisLivraison;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getStripeIntent(): ?string
    {
        return $this->stripeIntent;
    }

    public function setStripeIntent(string $stripeIntent): self
    {
        $this->stripeIntent = $stripeIntent;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function getAchats(): Collection
    {
        return $this->achats;
    }
}
