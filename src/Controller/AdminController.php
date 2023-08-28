<?php

namespace App\Controller;

use App\Core\Notification;
use App\Core\NotificationColor;

use App\Form\ProduitFormType;
use App\Form\CategorieCollection;
use App\Form\CategorieCollectionType;
use App\Form\ProduitModificationFormType;
use App\Form\SelectCommandeDetailsFormType;

use App\Entity\Categorie;
use App\Entity\Commande;
use App\Entity\Produit;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use DateTime;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Doctrine\ORM\Exception\ORMException;
use Exception;

class AdminController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/admin', name: 'app_admin')]
    public function admin(): Response
    {
        $currentUser = $this->getUser();

        return $this->render('admin/admin.html.twig', [
            'client' => $currentUser
        ]);
    }

    #[Route('/admin/categories', name: 'app_admin_categories')]
    public function categories(Request $request): Response
    {
        $currentUser = $this->getUser();

        $categories = $this->retrieveAllCategories();
        $categoriesCollection = new CategorieCollection($categories);

        try {
            $form = $this->createForm(CategorieCollectionType::class, $categoriesCollection);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $newCategories = $form->getData()->getCategories();

                foreach ($newCategories as $newCategorie) {
                    $this->em->persist($newCategorie);
                }

                $this->em->flush();

                $this->addFlash(
                    'notification-categorie',
                    new Notification('success', "Enregistrement des catégories réussi", NotificationColor::SUCCESS)
                );
            }
        } catch (Exception $th) {
            $this->addFlash(
                'notification-categorie',
                new Notification('error', 'Une catégorie ne peut être vide et ne peut pas être un doublon', NotificationColor::DANGER)
            );
        } catch (\Throwable $th) {
            $this->addFlash(
                'notification-categorie',
                new Notification('error', 'Erreur: ' . $th, NotificationColor::DANGER)
            );
        }

        return $this->render('admin/admin-categories.html.twig', [
            'client' => $currentUser,
            'categories' => $categories,
            'formulaire' => $form->createView()
        ]);
    }

    #[Route('/admin/nouveau-produit', name: 'app_admin_nouveau')]
    public function nouveau(Request $request, SluggerInterface $slugger): Response
    {
        $currentUser = $this->getUser();

        try {
            $produit = new Produit();
            $form = $this->createForm(ProduitFormType::class, $produit);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $picture = $form->get('imagePath')->getData();

                if ($picture != null) {

                    $originalFilename = pathinfo($picture->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . "-" . uniqid() . "." . $picture->guessExtension();

                    try {
                        $picture->move(
                            $this->getParameter('produit_picture_directory'),
                            $newFilename
                        );

                        $produit->setImagePath("/images/produits/" . $newFilename);
                        $this->em->persist($currentUser);
                        $this->em->flush();
                    } catch (FileException $e) {
                        $this->addFlash(
                            'notification-nouveau',
                            new Notification('error', "Erreur FileException: " . $e, NotificationColor::DANGER)
                        );
                    } catch (ORMException $e) {
                        $this->addFlash(
                            'notification-nouveau',
                            new Notification('error', "Erreur ORMException" . $e, NotificationColor::DANGER)
                        );
                    }
                }

                //Pour contrer la classe MoneyType du form
                $prix = $produit->getPrix();
                $produit->setPrix($prix / 100);

                $this->em->persist($produit);
                $this->em->flush();

                $this->addFlash(
                    'notification-nouveau',
                    new Notification('success', "Succès: Ajout du produit #" . $produit->getIdProduit(), NotificationColor::SUCCESS)
                );
                return $this->redirectToRoute('app_admin_nouveau');
            }
        } catch (\Throwable $th) {
            $this->addFlash(
                'notification-nouveau',
                new Notification('error', 'Erreur:' . $th, NotificationColor::DANGER)
            );
        }

        return $this->render('admin/admin-nouveau.html.twig', [
            'client' => $currentUser,
            'formulaire' => $form->createView()
        ]);
    }

    #[Route('/admin/produits', name: 'app_admin_produits')]
    public function produits(): Response
    {
        $currentUser = $this->getUser();
        $produits = $this->retrieveAllProduits();

        return $this->render('admin/admin-produits.html.twig', [
            'client' => $currentUser,
            'produits' => $produits
        ]);
    }

    #[Route('/admin/produits/{idProduit}', name: 'app_admin_produit_details')]
    public function produitDetails($idProduit, Request $request, SluggerInterface $slugger): Response
    {
        $currentUser = $this->getUser();

        try {
            $produit = $this->em->getRepository(Produit::class)->findBy(['idProduit' => $idProduit])[0];
            $possedeUneImageEnBD = $produit->getImagePath();

            //Pour contrer la classe MoneyType du form
            $prix = $produit->getPrix();
            $produit->setPrix($prix * 100);

            $form = $this->createForm(ProduitModificationFormType::class, $produit);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                //Une image dans le form
                $picture = $form->get('imagePath')->getData();

                //Si j'ai une image dans le form, on l'applique
                if ($picture != null) {
                    try {
                        $originalFilename = pathinfo($picture->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = $safeFilename . "-" . uniqid() . "." . $picture->guessExtension();

                        $picture->move(
                            $this->getParameter('produit_picture_directory'),
                            $newFilename
                        );
                        $produit->setImagePath("/images/produits/" . $newFilename);
                    } catch (FileException $e) {
                        $this->addFlash(
                            'notification-produit-detail',
                            new Notification('error', "Erreur FileException: " . $e, NotificationColor::DANGER)
                        );
                    } catch (ORMException $e) {
                        $this->addFlash(
                            'notification-produit-detail',
                            new Notification('error', "Erreur ORMException " . $e, NotificationColor::DANGER)
                        );
                    }
                }
                //Si je n'ai pas d'image dans le form, mais que j'ai une image en BD
                else if ($possedeUneImageEnBD != null) {
                    $produit->setImagePath($possedeUneImageEnBD);
                }

                //Pour contrer la classe MoneyType du form
                $prix = $produit->getPrix();
                $produit->setPrix($prix / 100);

                $this->em->persist($produit);
                $this->em->flush();

                $this->addFlash(
                    'notification-produit-detail',
                    new Notification('success', "Succès: Modification du produit #" . $produit->getIdProduit(), NotificationColor::SUCCESS)
                );
            }
        } catch (\Throwable $th) {
            $this->addFlash(
                'notification-produit-detail',
                new Notification('error', 'Erreur: Mauvaise entrée dans un champ', NotificationColor::DANGER)
            );
        }

        return $this->render('admin/admin-produit-details.html.twig', [
            'client' => $currentUser,
            'produit' => $produit,
            'formulaire' => $form->createView()
        ]);
    }

    #[Route('/admin/commandes', name: 'app_admin_commandes')]
    public function commandes(): Response
    {
        $currentUser = $this->getUser();
        $commandes = $this->retrieveAllCommandes();
        $commandes = array_reverse($commandes);

        return $this->render('admin/admin-commandes.html.twig', [
            'client' => $currentUser,
            'commandes' => $commandes
        ]);
    }

    #[Route('/admin/commandes/{idCommande}', name: 'app_admin_commande_details')]
    public function commandeDetail($idCommande, Request $request): Response
    {
        $currentUser = $this->getUser();
        $commande = $this->em->getRepository(Commande::class)->findBy(['idCommande' => $idCommande])[0];

        $form = $this->createForm(SelectCommandeDetailsFormType::class, $commande);
        $form->handleRequest($request);

        $etat = $form->get('etat')->getData();

        try {
            if ($form->isSubmitted()) {
                //Si la commande est livrée
                if ($commande->getEtat() == 'Livrée') {
                    $commande->setDateLivraison(new DateTime((date("Y-m-d H:i:s", time()))));
                }
                //Si la commande n'est pas livrée
                else {
                    $commande->setDateLivraison(null);
                }
                $commande->setEtat($etat);
                $this->em->persist($commande);
                $this->em->flush();

                $this->addFlash(
                    'notification-commande-detail',
                    new Notification('success', "Succès: La commande #" . $commande->getIdCommande() . " a bien changé d'état à \"" . $commande->getEtat() . "\"", NotificationColor::SUCCESS)
                );
            }
        } catch (\Throwable $th) {
            $this->addFlash(
                'notification-commande-detail',
                new Notification('error', "Une erreur est survenue", NotificationColor::DANGER)
            );
        }

        return $this->render('admin/admin-commande-details.html.twig', [
            'client' => $currentUser,
            'commande' => $commande,
            'etatCommande' => $commande->getEtat(),
            'formulaire' => $form->createView()
        ]);
    }

    //--------------------------------------------------------------

    private function retrieveAllCategories()
    {
        return $this->em->getRepository(Categorie::class)->findAll();
    }

    private function retrieveAllProduits()
    {
        return $this->em->getRepository(Produit::class)->findAll();
    }

    private function retrieveAllCommandes()
    {
        return $this->em->getRepository(Commande::class)->findAll();
    }
}
