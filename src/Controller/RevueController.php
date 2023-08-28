<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Constantes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RevueController extends AbstractController
{
    private $panierList;

    #[Route('/revue', name: 'app_revue')]
    public function revue(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $currentUser = $this->getUser();

        $fraisLivraison = Constantes::FRAIS_LIVRAISON;
        $TPS = Constantes::TPS;
        $TVQ = Constantes::TVQ;

        $this->initSession($request);

        $nbArticles = $this->panierList->calculerNbArticles();

        if ($nbArticles == 0) {
            return $this->redirectToRoute('app_connexion');
        }

        return $this->render('revue/revue.html.twig', [
            'client' => $currentUser,
            'panierlist' => $this->panierList,
            'fraisLivraison' => $fraisLivraison,
            'TPS' => $TPS,
            'TVQ' => $TVQ,
        ]);
    }

    private function initSession(Request $request)
    {
        $session = $request->getSession();

        $this->panierList = $session->get('panierlist');

        $session->set('panierlist', $this->panierList);
    }
}
