<?php

namespace App\Entity;

use App\Entity\Constantes;

class Panier
{
    //Les achats dans le panier
    private $achats = [];

    //Ajouter un achat dans le panier. Si il existe déjà dans le panier, incrémenter son nombre de 1
    public function ajouterAchat($produit, $quantite)
    {
        $exist = $this->estDansPanier($produit);

        //Si le produit existe
        if ($exist) {
            //Pour chaque produit dans le panier
            foreach ($this->achats as $achat) {
                //On trouve le produit en question
                if ($achat->getProduit()->getIdProduit() == $produit->getIdProduit()) {
                    $achat->setQuantite($achat->getQuantite() + 1);
                }
            }
        }
        //Si le produit n'existe pas
        else {
            $unAchat = new Achat($produit, $quantite);
            $this->achats[] = $unAchat;
        }
    }

    //On fait une boucle sur sur tous les achats. Pour chaque achats, on regarde le id de son produit. Si le id est le même que celui qu'on veut supprimer, on retire le produit. On utilise key comme index.
    public function supprimerAchat($idProduit)
    {
        foreach ($this->achats as $key => $achat) {
            if ($achat->getProduit()->getIdProduit() == $idProduit) {
                unset($this->achats[$key]);
            }
        }
    }

    public function getAchats()
    {
        return $this->achats;
    }

    //Vider la liste d'achats
    public function viderAchats()
    {
        unset($this->achats);
    }

    //Mettre à jour le panier d'achat
    public function mettreAJourPanier($nouveauxAchats)
    {

        if (count($this->achats) > 0) {

            $achatQtes = $nouveauxAchats["txtQte"];

            foreach ($this->achats as $key => $achat) {
                $nouvelleQte = (int)$achatQtes[$key];

                if (is_string($nouvelleQte)) {
                    unset($this->achats[$key]);
                } else if ($nouvelleQte < 1) {
                    unset($this->achats[$key]);
                } else {
                    $achat->majAchat($nouvelleQte);
                }
            }
        }
    }

    // ----------------------------------------------------------------------

    //Vérifier si un item est déjà dans la liste d'achat
    private function estDansPanier($produit): bool
    {

        //Par défaut, on concidère qu'il n'y ai pas.
        $exist = false;

        //On fait le tour des achats pour voir si le preoduit est là.
        foreach ($this->achats as $achat) {
            if ($achat->getProduit()->getIdProduit() == $produit->getIdProduit()) {
                $exist = true;
            }
        }

        return $exist;
    }

    public function calculerPanier()
    {
        $sousTotal = 0;
        foreach ($this->achats as $achat) {
            $sousTotal = $sousTotal + ($achat->getPrixAchat() * $achat->getQuantite());
        }

        return $sousTotal;
    }

    public function calculerNbArticles()
    {
        $nbArticles = 0;

        foreach ($this->achats as $achat) {
            $nbArticles = $nbArticles + $achat->getQuantite();
        }

        return $nbArticles;
    }

    public function calculerTPS()
    {
        $avantTPS = 0;
        $TPS = Constantes::TPS;

        foreach ($this->achats as $achat) {
            $avantTPS = $avantTPS + ($achat->getPrixAchat() * $achat->getQuantite());
        }

        return ($avantTPS * $TPS / 100);
    }

    public function calculerTVQ()
    {
        $avantTVQ = 0;
        $TVQ = Constantes::TVQ;

        foreach ($this->achats as $achat) {
            $avantTVQ = $avantTVQ + ($achat->getPrixAchat() * $achat->getQuantite());
        }

        return ($avantTVQ * $TVQ / 100);
    }

    public function calculerGrandTotal()
    {
        $avantTout = 0;
        $TPS = Constantes::TPS;
        $TVQ = Constantes::TVQ;
        $fraisLivraison = Constantes::FRAIS_LIVRAISON;

        foreach ($this->achats as $achat) {
            $avantTout = $avantTout + ($achat->getPrixAchat() * $achat->getQuantite());
        }

        return $avantTout + ($avantTout * $TPS / 100) + ($avantTout * $TVQ / 100) + $fraisLivraison;
    }
}
