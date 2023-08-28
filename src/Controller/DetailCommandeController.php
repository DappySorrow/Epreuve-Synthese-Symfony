<?php

namespace App\Controller;

use App\Entity\Commande;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DetailCommandeController extends AbstractController
{
    private $em = null;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->em = $doctrine->getManager();
    }

    #[Route('/commande/{idCommande}', name: 'app_detail_commande')]
    public function detailCommande($idCommande): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $currentUser = $this->getUser();
        $commande = $this->em->getRepository(Commande::class)->find($idCommande);

        //Si on essaie de voir la commande d'une autre personne
        try {
            if(!($currentUser->getIdClient() == $commande->getClient()->getIdClient())){
                return $this->redirectToRoute('app_commandes');
            }
        } catch (\Throwable $th) {
            return $this->redirectToRoute('app_commandes');
        }
        
        
        return $this->render('detail_commande/detail_commande.html.twig', [
            'client' => $currentUser,
            'commande' => $commande
        ]);
    }
}