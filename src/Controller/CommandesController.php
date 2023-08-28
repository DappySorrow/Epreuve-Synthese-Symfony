<?php

namespace App\Controller;

use App\Entity\Commande;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandesController extends AbstractController
{
    private $em = null;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->em = $doctrine->getManager();
    }

    #[Route('/commandes', name: 'app_commandes')]
    public function commandes(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $currentUser = $this->getUser();

        $commandes = $this->em->getRepository(Commande::class)->findBy(['client' => $currentUser->getIdClient()], ['dateCommande' => 'ASC']);

        return $this->render('commandes/commandes.html.twig', [
            'client' => $currentUser,
            'commandes' => $commandes
        ]);
    }
}
