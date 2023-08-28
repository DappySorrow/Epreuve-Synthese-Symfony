<?php

namespace App\Controller;

use App\Entity\Achat;
use App\Entity\Commande;
use App\Entity\Produit;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Core\Notification;
use App\Core\NotificationColor;

use Stripe;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeController extends AbstractController
{
    private $em = null;
    private $panierList;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->em = $doctrine->getManager();
    }

    #[Route('/stripe', name: 'app_stripe')]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $this->initSession($request);

        $user = $this->getUser();

        \Stripe\Stripe::setApiKey($_ENV["STRIPE_SECRET"]);

        $successURL = $this->generateUrl('stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . "?stripe_id={CHECKOUT_SESSION_ID}";

        $sessionData = [

            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'unit_amount' => (int)(ceil($this->panierList->calculerGrandTotal() * 100)),
                    'currency' => 'CAD',
                    'product_data' => ['name' => "Dragon's Keep"]
                ]
            ]],

            'customer_email' => $user->getCourriel(),
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'success_url' => $successURL,
            'cancel_url' => $this->generateUrl('stripe_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        $checkoutSession = \Stripe\Checkout\Session::create($sessionData);
        return $this->redirect($checkoutSession->url, 303);
    }

    #[Route('/stripe-success', name: 'stripe_success')]
    public function stripeSuccess(Request $request): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $this->initSession($request);

        try {
            //Valider le token Stripe reçu dans l’URL
            //Récupérer le Stripe Intent en questionnant l’API Stripe
            $stripe = new \Stripe\StripeClient($_ENV["STRIPE_SECRET"]);
            $stripeSessionId = $request->query->get('stripe_id');
            $sessionStripe = $stripe->checkout->sessions->retrieve($stripeSessionId);
            $paymentIntent = $sessionStripe->payment_intent;

            //Créer une commande à partir du panier de l’utilisateur
            //Associer l’utilisateur actuellement connecté à la commande
            $commande = new Commande($user, $this->panierList, $paymentIntent);

            foreach ($commande->getAchats() as $achat) {
                //Merge le produit
                $newProduit = $this->em->merge($achat->getProduit());

                //Vendre x Produit
                $vente = $achat->getQuantite();
                $stock = $newProduit->getQuantiteEnStock();

                $newProduit->setQuantiteEnStock($stock - $vente);

                //Mettre à jour l’inventaire des produits présents dans la commande ET Si un produit est en rupture d’inventaire, vous devez pouvoir en informer l’utilisateur
                if ($stock - $vente > 0) {
                    $this->addFlash(
                        'notifications_commandes',
                        new Notification("success", "Votre commande pour " . $achat->getProduit()->getNom() . " est en préparation!", NotificationColor::INFO)
                    );
                } else {
                    $this->addFlash(
                        'notifications_commandes',
                        new Notification("success", "Votre commande pour " . $achat->getProduit()->getNom() . " va prendre plus de temps!", NotificationColor::DANGER)
                    );
                }

                //Achat->setProduit($p)
                $achat->setProduit($newProduit);
            }

            //Sauvegarder la commande et faire la cascade
            $this->em->persist($commande);
            $this->em->flush();

            //Vider le panier
            $this->initSession($request);
            $this->panierList->viderAchats();
        } catch (\Exception $e) {
            return $this->redirectToRoute('app_profil');
        }

        //Rediriger l’utilisateur vers la page de détail de cette commande, avec un message indiquant si certains produits sont en rupture de stock.
        return $this->redirectToRoute('app_commandes');
    }

    #[Route('/stripe-cancel', name: 'stripe_cancel')]
    public function stripeCancel(): Response
    {
        return $this->redirectToRoute('app_panier');
    }

    private function initSession(Request $request)
    {
        $session = $request->getSession();

        $this->panierList = $session->get('panierlist');

        $session->set('panierlist', $this->panierList);
    }
}
