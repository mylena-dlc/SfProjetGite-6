<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\SendMailService;
use App\Repository\GiteRepository;
use App\Repository\UserRepository;
use App\Form\ResetPasswordFormType;
use App\Repository\PeriodRepository;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use App\Form\ResetPasswordRequestFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

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
    * Fonction de réinisialisation de mot de passe
    */

    #[Route(path: '/reset-password', name: 'app_reset_password')]
    public function resetPassword(Request $request, UserRepository $userRepository, TokenGeneratorInterface $tokenGeneratorInterface, EntityManagerInterface $em, SendMailService $mail): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            // Recherche de l'user par son email
            $user = $userRepository->findOneByEmail($form->get('email')->getData());

            // Si il y a bien un user
            if($user) {
                // On génère un token de réinitialisation qu'on modifie pour l'user en BDD
                $token = $tokenGeneratorInterface->generateToken();
                $user->setResetToken($token);
                $em->persist($user);
                $em->flush();

                // On génère un lien de réinitialisation du mot de passe
                $url = $this->generateUrl('reset_pass', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                // Création des données du mail
                $context = compact('url', 'user');

                // Envoi du mail
                $mail->send(
                    'no-reply@giteraindupair.fr',
                    $user->getEmail(),
                    'Réinistialisation de mot de passe',
                    'password-reset',
                    $context
                );

                $this->addFlash('succes', 'Email envoyé avec succès');
                return $this->redirectToRoute('app_login');

            }
            // si $user est null
            $this->addFlash('error', 'Un problème est survenu');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset-password-request.html.twig', [
            'requestPassForm' => $form->createView(),
        ]);

    }

    #[Route(path: '/reset-password/{token}', name: 'reset_pass')]
    public function resetPass(string $token, Request $request, UserRepository $userRepository, EntityManager $em, UserPasswordHasherInterface $passwordHasher): Response {

        // On vérifie si on a ce token dans la base
        $user = $userRepository->findOnByResetToken($token);

        $form->handlerequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            // On efface le token
            $user->setResetToken('');
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user, 
                    $form->get('password')->getData()
                )
            );
            $em->persist($user);
            $em->flush();

            $this->addFlash('succes', 'Mot de passe modifié avec succès.');
            return $this->redirectToRoute('app_login');
        }

        if($user) {
            $form = $this->createForm(ResetPasswordFormType::class);

            return $this->render('security/reset-password.html.twig', [
                'passForm' => $form->createView()
            ]);
        }

        $this->addFlash('error', 'Jeton invalide');
        return $this->redirectToRoute('app_login');
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
