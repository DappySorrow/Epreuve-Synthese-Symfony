<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Categorie;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CatalogueController extends AbstractController
{
    private $em = null;
    #[Route('/', name: 'app_catalogue')]
    public function indexRoute(Request $request, ManagerRegistry $doctrine): Response
    {
        $currentUser = $this->getUser();

        //Pour les fonctions utilitaires
        $this->em = $doctrine->getManager();

        //Pour la barre de recherche
        $searchField = $request->request->get('search_field');

        $categorie = $request->query->get('categorie');
        $categories = $this->retrieveAllCategories();

        $produits = $this->retrieveCategories($categorie, $searchField);
        $tousProduits = $this->retrieveAllProduit();


        return $this->render('catalogue/catalogue.html.twig', [
            'produits' => $produits, 
            'categories' => $categories, 
            'tousProduits' => $tousProduits,
            'client' => $currentUser
        ]);
    }

    #[Route('/produits/{idProduit}', name: 'produit_modal')]
    public function infoProduit($idProduit, Request $request, ManagerRegistry $doctrine): Response
    {
        $this->em = $doctrine->getManager();

        $produit = $this->em->getRepository(Produit::class)->find($idProduit);

        return $this->render('catalogue/produit.modal.twig', [
            'produit' => $produit
        ]);
    }

    //----------------------------------------------------------------

    private function retrieveAllProduit()
    {
        return $this->em->getRepository(Produit::class)->findAll();
    }

    private function retrieveAllCategories()
    {
        return $this->em->getRepository(Categorie::class)->findAll();
    }

    private function retrieveCategories($categorie, $searchField)
    {
        return $this->em->getRepository(Produit::class)->findWithCriteria($categorie, $searchField);
    }
}
