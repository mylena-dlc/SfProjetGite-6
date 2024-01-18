<?php

namespace App\Controller;

use App\Entity\Gite;
use App\Entity\User;
use App\Entity\Period;
use App\Entity\Review;
use App\Form\GiteType;
use App\Form\UserType;
use App\Entity\Activity;
use App\Form\PeriodType;
use App\Form\ReviewType;
use App\Form\ActivityType;
use App\Form\ReservationViewType;
use App\Repository\GiteRepository;
use App\Repository\UserRepository;
use App\Repository\PeriodRepository;
use App\Repository\ReviewRepository;
use App\Repository\PictureRepository;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashboardController extends AbstractController
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

    /**
     * @var ReviewRepository
     */
    private $reviewRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var ActivityRepository
     */
    private $activityRepository;

    public function __construct(GiteRepository $giteRepository, EntityManagerInterface $em, PictureRepository $pictureRepository, ReservationRepository $reservationRepository, PeriodRepository $periodRepository, ReviewRepository $reviewRepository, UserRepository $userRepository, ActivityRepository $activityRepository)
    {
        $this->giteRepository = $giteRepository;
        $this->em = $em;
        $this->pictureRepository = $pictureRepository;
        $this->reservationRepository = $reservationRepository;
        $this->reviewRepository = $reviewRepository;
        $this->periodRepository = $periodRepository;
        $this->userRepository = $userRepository;
        $this->activityRepository = $activityRepository;
    }

    #[Route('admin/dashboard', name: 'app_dashboard')]
    public function index(Request $request): Response

    {
        // Recherche des 5 réservations les plus récentes pour l'index du dashboard
        $reservations = $this->reservationRepository->findBy([], ['reservationDate' => 'ASC'], 5);

        return $this->render('dashboard/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    /**
    * Fonction pour afficher les infos du gîte
    */

    #[Route('admin/gite', name: 'app_gite')]
    public function showGite(): Response
    {
        $gites = $this->giteRepository->findAll();
        return $this->render('gite/index.html.twig', [
            'gites' => $gites,
        ]);
    }


    /**
    * Fonction pour ajouter ou éditer un gîte
    */

    #[Route('admin/gite/new', name: 'new_gite')]
    #[Route('admin/gite/{id}/edit', name: 'edit_gite')]

    public function newEditGite(Gite $gite = null, Request $request): Response {
    
        if(!$gite) {
            $gite = new Gite();
        }
    
        $form = $this->createForm(GiteType::class, $gite);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $gite = $form->getData(); 
            // prepare en PDO
            $this->em->persist($gite);
            // execute PDO
            $this->em->flush();

            return $this->redirectToRoute('app_gite');
        }

        return $this->render('gite/new.html.twig', [
            'form' => $form,
            'edit' => $gite->getId(),
        ]);
    }  

    
    /**
    * Fonction pour supprimer un gîte
    */

    #[Route('admin/gite/{id}/delete', name: 'delete_gite')]
    public function deleteGite(Gite $gite) {

        // pour préparé l'objet $gite à supprimer (enlever cet objet de la collection)
        $this->em->remove($gite);
        // flush va faire la requête SQL et concretement supprimer l'objet de la BDD
        $this->em->flush();

        $this->addFlash('success', "Gîte supprimé avec succès !");

        return $this->redirectToRoute('app_gite');
    }



    // /**
    // * Fonction pour afficher, ajouter ou éditer une période
    // */

    #[Route('admin/period', name: 'app_period')]

    public function newPeriod(Request $request): Response {

        $periods = $this->periodRepository->findAll([], ['startDate' => 'ASC']);

        $newPeriod = new Period();
        
        // On recupère l'id du gite et on l'associe à la période
        $gite = $this->giteRepository->find(4);
        $newPeriod->setGite($gite);

        $form = $this->createForm(PeriodType::class, $newPeriod);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newPeriod = $form->getData(); 
  
            $startDate = $newPeriod->getStartDate();
            $endDate = $newPeriod->getEndDate();

            // Vérification des dates en BDD
            $overlappingPeriods = $this->periodRepository->findOverlappingPerriods($startDate, $endDate, $newPeriod->getId());

            if (!empty($overlappingPeriods)) {
                $this->addFlash('error', 'Les dates de début et de fin chevauchent une période existante.');
            } else {
            // Si le formulaire est valide et qu'il n'y a pas de chevauchement, enregistrez la nouvelle période
            $this->em->persist($newPeriod);
            $this->em->flush();

            $this->addFlash('success', 'La période a été ajoutée avec succès.');
            return $this->redirectToRoute('app_period');
        }
    }

        return $this->render('period/index.html.twig', [
            'form' => $form,
            'periods' => $periods
        ]);
    }  



    /**
    * Fonction pour afficher les réservations passées
    */

    #[Route('admin/dashboard/previous-reservations', name: 'app_previous_reservations')]
    public function previousReservations(): Response
    {
        $previousReservations = $this->reservationRepository->findPreviousReservations();
    
        return $this->render('dashboard/previous-reservations.html.twig', [
            'previousReservations' => $previousReservations,
        ]);
    }
    


    /**
    * Fonction pour afficher les réservations à venir
    */

    #[Route('admin/dashboard/upcoming-reservations', name: 'app_upcoming_reservations')]
    public function upcomingReservations(): Response
    {
        $upcomingReservations = $this->reservationRepository->findUpcomingReservations();
    
        return $this->render('dashboard/upcoming-reservations.html.twig', [
            'upcomingReservations' => $upcomingReservations,
        ]);
    }



    /**
    * Fonction pour ajouter un avis
    */

    #[Route('security/{id}/writeReview{reservation_id}', name: 'app_write_review')]
    public function writeReview(User $user, Request $request, int $id, $reservation_id): Response
    {
        $userSession = $this->getUser();
        $user = $this->userRepository->findOneBy(['id' => $id]);

        if($userSession == $user) {
            $review = new Review();

            $form = $this->createForm(ReviewType::class, $review);
            $form->handleRequest($request);

            // On récupère l'id de l'utilisateur connecté
            $user = $this->getUser();
            $review->setUser($user);

            // On récupère l'id de la réservation concernée
            $reservation = $this->reservationRepository->find($reservation_id);
            $review->setReservation($reservation);

            if ($form->isSubmitted() && $form->isValid()) {
            
                $review = $form->getData();
                $this->em->persist($review);
                $this->em->flush();
                $this->addFlash('success', "Avis ajouté avec succès. Merci d\'avoir partagé votre expérience avec nous.", false);

                $userId = $user->getId();
                return $this->redirectToRoute('app_profil', ['id' => $userId]); 
            }

            return $this->render('security/writeReview.html.twig', [
                'form' => $form->createView(),
            ]);
        }
        
        if($userSession != $user) {
            $this->addFlash('error', 'Accès refusé');
            return $this->redirectToRoute('app_home');
        }
        
        return $this->redirectToRoute('app_login');

    }


    /**
    * Fonction pour afficher les avis
    */

    #[Route('admin/dashboard/review', name: 'app_review')]
    public function showReview(): Response
    {
        // Recherche de tous les avis non vérifiés par l'admin
        $unverifiedReviews = $this->reviewRepository->findBy(['is_verified' => 0]);

        
        return $this->render('dashboard/review.html.twig', [
            'unverifiedReviews' => $unverifiedReviews
        ]);
    }
  

    /**
    * Fonction pour valider un avis par l'administrateur
    */

    #[Route('admin/dashboard/review/verify/{id}', name: 'app_verify_review')]
    public function verifyReview(Request $request, int $id): Response
    {
        $review = $this->reviewRepository->find($id);

        // Mettre à jour le champ is_verified à 1
        $review->setIsVerified(1);

        // Récupération de la réponse de l'admin depuis el formulaire
        $response = $request->request->get('response');
        $review->setResponse($response);
    
        
        $this->em->flush();

        $this->addFlash('success', 'Avis validé avec succès.');

        // Rediriger vers la page des avis à vérifier
        return $this->redirectToRoute('app_review');
    }


    /**
    * Fonction pour afficher ou ajouter une activité
    */

    #[Route('admin/activity/', name: 'app_activity')]

    public function newActivity(Request $request): Response {
    
        $activities = $this->activityRepository->findAll();

        $newActivity = new Activity();

        // On recupère l'id du gite et on l'associe à l'activité
        $gite = $this->giteRepository->find(4);
        $newActivity->setGite($gite);

        $form = $this->createForm(ActivityType::class, $newActivity);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $pictureFile = $form->get('picture')->getData();

            if ($pictureFile) {
                $newFilename = uniqid().'.'.$pictureFile->guessExtension();
                $newFilePath = $this->getParameter('pictures_directory').'/'.$newFilename;

                try {
                    $pictureFile->move(
                        $this->getParameter('pictures_directory'),
                        $newFilename
                    );                
                    
                    $newActivity->setpicture('uploads/' . $newFilename);

                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement du fichier.');
                } catch (AccessDeniedException $e) {
                    $this->addFlash('error', 'Accès refusé au répertoire de stockage des images.');
                }
            }

            $newActivity = $form->getData(); 

            $this->em->persist($newActivity);
            $this->em->flush();

            $this->addFlash('success', 'L\'activité a été ajoutée avec succès !');
            return $this->redirectToRoute('app_activity');
        }

        return $this->render('activity/index.html.twig', [
            'form' => $form,
            'activities' => $activities,
        ]);
    }  

}

