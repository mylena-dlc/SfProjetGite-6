<?php

namespace App\Controller;

use App\Repository\ReviewRepository;
use App\Repository\PictureRepository;
use App\Repository\CategoryRepository;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class HomeController extends AbstractController
{

    /**
     * @var PictureRepository
     */
    private $pictureRepository;

    /**
     * @var ReservationRepository
     */
    private $reservationRepository;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var ReviewRepository
     */
    private $reviewRepository;

    
    public function __construct(PictureRepository $pictureRepository, ReservationRepository $reservationRepository, CategoryRepository $categoryRepository, ReviewRepository $reviewRepository)
    {
        $this->pictureRepository = $pictureRepository;
        $this->reservationRepository = $reservationRepository;
        $this->categoryRepository = $categoryRepository;
        $this->reviewRepository = $reviewRepository;
    }


    #[Route('/', name: 'app_home')]
    public function index(httpClientInterface $httpClient): Response
    {

        // Affichage de la galerie d'images
        $categories = $this->categoryRepository->findAll();

        $categoryFirstPictures = [];

        foreach($categories as $category) {
            $firstPicture = $this->pictureRepository->findOneBy(['category' => $category], ['id' => 'ASC']);
            if ($firstPicture) {
                $categoryFirstPictures[$category->getName()] = $firstPicture;
            }
        }

        // Affichage des dates déjà réservées
        $reservations = $this->reservationRepository->findAll();

        $reservedDates = [];

        foreach($reservations as $reservation) {
            $reservedDates[] = [
                'id' => $reservation->getId(),
                'arrivalDate' => $reservation->getArrivalDate()->format('Y-m-d'),
                'departureDate' => $reservation->getDepartureDate()->format('Y-m-d'),
            ];
        }

         $data = json_encode($reservedDates);

         // Affichage des avis
         $reviews = $this->reviewRepository->findVerifiedReviews();

         // Affichage de la moyenne des notes
         $averageRating = $this->reviewRepository->averageRating();

         $description = 'Réservez votre location de vacances en Alsace, dans notre gîte de charme à Orbey. Hébergement rénové avec bain nordique pour un séjour inoubliable au cœur de la nature.';

        return $this->render('home/index.html.twig', [
            'reservedDates' => $data,
            'categoryFirstPictures' => $categoryFirstPictures,
            'categories' => $categories,
            'reviews' => $reviews,
            'averageRating' => $averageRating,
            'description' => $description,
        ]);
}


    /**
    * Fonction de redirection vers les mentions légales
    */

    #[Route('/mentions-legales', name: 'app_mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('home/mentions-legales.html.twig');
    }


    /**
    * Fonction de redirection vers la politique de confidentialité
    */

    #[Route('/politique-confidentialite', name: 'app_politique-confidentialite')]
    public function politiqueConfidentialite(): Response
    {
        return $this->render('home/politique-confidentialite.html.twig');
    }


    /**
    * Fonction de redirection vers les conditions générales de vente
    */

    #[Route('/conditions-generales-vente', name: 'app_condition_generales_vente')]
    public function conditionsGeneralesVente(): Response
    {
        return $this->render('home/cgv.html.twig');
    }


    /**
    * Fonction pour afficher tous les avis
    */

    #[Route('home/reviews', name: 'app_all_reviews')]
    public function showAllReviews(Request $request): Response
    {
    
        // Système de pagination des avis, 3 sont affichés par page
        // Récupération du numéro de page, par défaut 1
        $page = $request->query->getInt('page', 1); 
        $perPage = 3; 

        // Calcul du décalage offset (spécifie le point de départ à partir duquel on souhaite récupérer des éléments)
        $offset = ($page - 1) * $perPage;

        // Recherche de tous les avis déjà vérifiés par l'admin du plus récent au plus ancient
        $reviews = $this->reviewRepository->findBy(['is_verified' => 1],['creationDate' => 'DESC'],
        $perPage,
        $offset);

        $totalReviews = $this->reviewRepository->count(['is_verified' => 1]);

        $pagination = [
            'page' => $page,
            'pages' => ceil($totalReviews / $perPage), // Calcul du nombre total de pages nécéssaires, ceil arrondit le résultat à l'entier suppérieur
            'route' => 'app_all_reviews', 
        ];

        $description = 'Découvrez les avis authentiques des voyageurs
         qui ont séjourné dans notre gîte de charme en Alsace. 
         Un hébergement de qualité pour vos futures vacances à Orbey.';

        return $this->render('home/reviews.html.twig', [
            'reviews' => $reviews,
            'pagination' => $pagination,
            'description' => $description
        ]);
    }








}