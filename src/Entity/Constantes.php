<?php

namespace App\Entity;

class Constantes
{
    public const FRAIS_LIVRAISON = 9.99;
    public const TPS = 5;
    public const TVQ = 9.975;
}

class Etat
{
    public const PREPARATION = 'En préparation';
    public const ENVOYER = 'Envoyée';
    public const TRANSIT = 'En transit';
    public const LIVREE = 'Livrée';
}
