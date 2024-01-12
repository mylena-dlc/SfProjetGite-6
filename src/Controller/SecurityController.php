<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\GiteRepository;
use App\Repository\PeriodRepository;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
       /**
     * @var GiteRepository
     */
    private $giteRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var PictureRepository
     */
    private $pictureRepository;

    /**
     * @var PeriodRepository
     */
    private $periodRepository;

    /**
     * @var ReservationRepository
     */
    private $reservationRepository;


    public function __construct(GiteRepository $giteRepository, EntityManagerInterface $em, PictureRepository $pictureRepository, ReservationRepository $reservationRepository)
    {
        $this->giteRepository = $giteRepository;
        $this->em = $em;
        $this->pictureRepository = $pictureRepository;
        $this->reservationRepository = $reservationRepository;
    }


    /**
    * Fonction Login
    */

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }



    /**
    * Fonction de déconnection
    */

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }



    /**
    * Fonction pour afficher les détails d'un profil et les réservations à venir
    */
    
    #[Route(path: '/profil/{id}', name: 'app_profil')]

    public function profil(User $user, Request $request): Response
    {
    
        // Récupérez les réservations à venir de l'utilisateur
        $upcomingReservations = $this->reservationRepository->findUpcomingReservations();

        return $this->render('security/profil.html.twig', [
            'user' => $user,
            'upcomingReservations' => $upcomingReservations
        ]);    
    }


    /**
    * Fonction pour afficher les détails d'un profil
    */
    
    #[Route(path: '/profil/{id}/previous-reservations', name: 'app_profil_previous_reservations')]

    public function previousReservation(User $user, Request $request): Response
    {
        // Récupérez les réservations passées de l'utilisateur
        $previousReservations = $this->reservationRepository->findPreviousReservations();

        return $this->render('security/previous-reservations.html.twig', [
            'user' => $user,
            'previousReservations' => $previousReservations
        ]);    
    }



    /**
    * Fonction de suppresion d'un compte
    */

    #[Route(path: '/delete-account', name: 'app_delete_account')]
    public function deleteAccount(Request $request): Response
    {
        // Récupérer l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Gérer la suppression du compte 
        if ($user) {
            // Générer un token unique pour identifier de manière unique chaque compte supprimé
            $deleteToken = md5(uniqid());

            // Mettre à jour les champs de l'utilisateur
            $user->setemail('utilisateur_supprime_' . $deleteToken);
            
            // Récupérer le mot de passe haché
            $password = $user->getPassword();
            $passwordHash = md5($password);

            $user->setpassword($passwordHash);

            // Récupérez les réservations de l'utilisateur
            $reservations = $this->reservationRepository->findBy(['user' => $user]);

            // Mettre à jour l'ID de l'utilisateur à NULL pour chaque réservation
            foreach ($reservations as $reservation) {
                $reservation->setUser(null);
                $this->em->persist($reservation);
            }

            $user->setRoles(['role_supprime']);

            // Mettre à jour l'entité dans la base de données
            $this->em->persist($user);
            $this->em->flush();
            
            // On récupère la session en cours afin de la supprimer
            // $session = $request->getSession();

            // unset($session);
            
            $this->addFlash('success', 'Votre compte a été supprimé avec succès.');

        } else {
            $this->addFlash('error', 'Impossible de supprimer le compte. Utilisateur non trouvé.');
        }

        // Rediriger l'utilisateur vers la page d'accueil
         return $this->redirectToRoute('app_home');
    }
}
