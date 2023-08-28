<?php

namespace App\Controller;

use App\Core\Notification;
use App\Core\NotificationColor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Form\UpdateInfosFormType;
use App\Form\UpdatePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function profil(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $currentUser = $this->getUser();

        $infos = $this->createForm(UpdateInfosFormType::class, $currentUser);
        $infos->handleRequest($request);

        $mdp = $this->createForm(UpdatePasswordFormType::class);
        $mdp->handleRequest($request);

        if ($infos->isSubmitted() && $infos->isValid()) {
            //Deux lignes important pour la mise à jour
            $entityManager->persist($currentUser);
            $entityManager->flush();

            $this->addFlash(
                'notification',
                new Notification('success', 'Le profil a été enregistré avec succès!', NotificationColor::INFO)
            );
        }

        if ($mdp->isSubmitted() && $mdp->isValid()) {

            $formPassword = $mdp->get('password')->getData();
            $pass = $mdp->get('motDePasse')->getData();

            if ($userPasswordHasher->isPasswordValid($currentUser, $formPassword)) {

                $encodedPassword = $userPasswordHasher->hashPassword($currentUser, $pass);

                $currentUser->setPassword($encodedPassword);

                $entityManager->persist($currentUser);
                $entityManager->flush();

                $this->addFlash(
                    'notification',
                    new Notification('success', 'Le mot de passe a été modifié avec succès!', NotificationColor::INFO)
                );
            } else {
                $this->addFlash(
                    'notification',
                    new Notification('error', "Le mot de passe actuel n'est pas valide", NotificationColor::DANGER)
                );
            }
        }

        return $this->render('profil/profil.html.twig', [
            'client' => $currentUser,
            'infos' => $infos->createView(),
            'mdp' => $mdp->createView()
        ]);
    }

    //-------------------------------------------------------------------------

    #[Route('/connexion', name: 'app_connexion')]
    public function connexion(AuthenticationUtils $authenticationUtils): Response
    {
        $currentUser = $this->getUser();

        //Si on a un utilisateur, on le redirige vers son profil
        if ($this->getUser()) {
            return $this->redirectToRoute('app_profil', [
                'client' => $currentUser
            ]);
        }

        //Si on a des erreurs 
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error != null && $error->getMessageKey() === 'Invalid credentials.') {
            $this->addFlash(
                'notification',
                new Notification('error', "Mauvaise combinaison d'identifiant et de mot de passe.", NotificationColor::WARNING)
            );
        }

        //Retourner le dernier email rentré
        return $this->render('profil/connexion.html.twig', [
            'dernierUtilisateur' => $authenticationUtils->getLastUsername(),
            'client' => $currentUser
        ]);
    }

    //-------------------------------------------------------------------------

    #[Route('/deconnexion', name: 'app_deconnexion')]
    public function deconnexion()
    {

        throw new \Exception("Ne pas oublier d'activer la deconnexion dans le main de security.yaml");
    }
}
