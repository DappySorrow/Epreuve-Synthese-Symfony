<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\Constantes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Core\Notification;
use App\Core\NotificationColor;

class PanierController extends AbstractController
{
    private $panierList;
    private $produit;
    private $em = null;

    private $fraisL;
    private $TPS;
    private $TVQ;

    private $sousTotal;
    private $qteTotal;

    #[Route('/panier', name: 'app_panier')]
    public function index(Request $request): Response
    {
        $currentUser = $this->getUser();

        $fraisLivraison = Constantes::FRAIS_LIVRAISON;
        $TPS = Constantes::TPS;
        $TVQ = Constantes::TVQ;

        $this->initSession($request);
        $session = $request->getSession();

        return $this->render('panier/panier.html.twig', [
            'client' => $currentUser,
            'produit' => $this->produit,
            'panierlist' => $this->panierList,
            'fraisLivraison' => $fraisLivraison,
            'TPS' => $TPS,
            'TVQ' => $TVQ,
            'sousTotal' => $this->panierList->calculerPanier(),
            'qteTotal' => $this->panierList->calculerNbArticles()
        ]);
    }

    #[Route('/panier/ajout/{idProduit}', name: 'panier_add', methods: ['POST'])]
    public function addPanier($idProduit, Request $request, ManagerRegistry $doctrine): Response
    {
        $this->initSession($request);
        $this->em = $doctrine->getManager();
        $produit = $this->em->getRepository(Produit::class)->find($idProduit);

        $this->panierList->ajouterAchat($produit, 1);

        $this->addFlash(
            'achat',
            new Notification('success', 'Le produit a été ajouté au panier', NotificationColor::INFO)
        );

        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/supprimer/{idProduit}', name: 'panier_delete')]
    public function supprimerAchat($idProduit, Request $request): Response
    {
        $this->initSession($request);
        $this->panierList->supprimerAchat($idProduit);
        $this->addFlash(
            'achat',
            new Notification('success', 'Le produit a été retiré du panier', NotificationColor::INFO)
        );
        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/vider', name: 'panier_vider')]
    public function viderAchats(Request $request)
    {
        $this->initSession($request);
        $this->panierList->viderAchats();
        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/update', name: 'panier_update')]
    public function mettreAJourAchats(Request $request)
    {
        $post = $request->request->all();
        $this->initSession($request);
        $action = $request->request->get('action');

        //rafraichir
        if ($action == "rafraichir") {
            $this->panierList->mettreAJourPanier($post);
            $this->addFlash(
                'achat',
                new Notification('success', 'Panier mis à jour', NotificationColor::INFO)
            );
        }
        //Vider
        else if ($action == "vider") {
            $this->initSession($request);
            $this->panierList->viderAchats();
            $this->addFlash(
                'achat',
                new Notification('success', 'Panier a été vidé', NotificationColor::INFO)
            );
        }

        return $this->redirectToRoute('app_panier');
    }

    //------------------------------------------------------

    private function initSession(Request $request)
    {
        $session = $request->getSession();

        $this->panierList = $session->get('panierlist', new Panier());

        $session->set('panierlist', $this->panierList);
    }
}
