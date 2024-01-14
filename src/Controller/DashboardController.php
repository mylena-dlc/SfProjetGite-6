<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Period;
use App\Entity\Review;
use App\Form\UserType;
use App\Form\PeriodType;
use App\Form\ReviewType;
use App\Form\ReservationViewType;
use App\Repository\GiteRepository;
use App\Repository\PeriodRepository;
use App\Repository\ReviewRepository;
use App\Repository\PictureRepository;
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

    public function __construct(GiteRepository $giteRepository, EntityManagerInterface $em, PictureRepository $pictureRepository, ReservationRepository $reservationRepository, PeriodRepository $periodRepository, ReviewRepository $reviewRepository)
    {
        $this->giteRepository = $giteRepository;
        $this->em = $em;
        $this->pictureRepository = $pictureRepository;
        $this->reservationRepository = $reservationRepository;
        $this->reviewRepository = $reviewRepository;
        $this->periodRepository = $periodRepository;

    }

    #[Route('admin/dashboard', name: 'app_dashboard')]
    public function index(Request $request): Response

    {
        // Recherche des 5 réservations les plus récentes pour l'index du dashboard
        $reservations = $this->reservationRepository->findBy([], ['reservationDate' => 'ASC'], 5);

        // Créez un formulaire pour le bouton "Enregistrer" unique
        $form = $this->createFormBuilder()
            ->add('save', SubmitType::class, ['label' => 'Enregistrer'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($reservations as $reservation) {
                // Mettez à jour l'état "view" uniquement pour les réservations dont la case à cocher est cochée
                if ($request->request->get('reservation_view_' . $reservation->getId())) {
                    $reservation->setView(true);                        
                    $this->em->flush();
                }
            }     
        
            $this->addFlash('success', 'Les états "vue" des réservations ont été modifiés avec succès.');

            $viewForms[$reservation->getId()] = $form->createView();
        }

        return $this->render('dashboard/index.html.twig', [
            'reservations' => $reservations,
            'form' => $form->createView(), // Ajoutez ce formulaire à la vue

        ]);
    }

    /**
    * Fonction pour afficher les infos du gîte
    */

    #[Route('/gite', name: 'app_gite')]
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

    #[Route('/gite/new', name: 'new_gite')]
    #[Route('/gite/{id}/edit', name: 'edit_gite')]

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

    #[Route('/gite/{id}/delete', name: 'delete_gite')]
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

    #[Route('/period', name: 'app_period')]

    public function newPeriod(Request $request): Response {

        $periods = $this->periodRepository->findAll([], ['startDate' => 'ASC']);

        $newPeriod = new Period();
        
        // on recupère l'id du gite
        $gite = $this->giteRepository->find(4);

        // Associez le gîte à la période
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

    #[Route('security/whriteReview{reservation_id}', name: 'app_write_review')]
    public function writeReview(Request $request, $reservation_id): Response
    {
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



    /**
    * Fonction pour afficher les avis
    */

    #[Route('admin/dashboard/review', name: 'app_review')]
    public function showReview(): Response
    {
        // Recherche de tous les avis non vérifiés par l'admin
        $unverifiedReviews = $this->reviewRepository->findBy(['is_verified' => 0]);

        // Recherche de tous les avis déjà vérifiés
        $reviews = $this->reviewRepository->findBy(['is_verified' => 1]);
    
        
        return $this->render('dashboard/review.html.twig', [
            'unverifiedReviews' => $unverifiedReviews,
            'reviews' => $reviews,
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

}

