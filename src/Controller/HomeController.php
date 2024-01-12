<?php

namespace App\Controller;

use App\Repository\ReviewRepository;
use App\Repository\PictureRepository;
use App\Repository\CalendarRepository;
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

        // on récupère toutes les catégories
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




        // // Affichage des posts Instagram
        //  $userId = '25081345021464048';
        //  $accessToken = 'IGQWRPZAnhnQ05LT2pQSkNad3lERW0tNVotMDRRa282d0pINmN5MDNKUkxvU1phT29yWE5fMkw4b0V1X1NEM1BvS3FUbVA2ajhRTzE0dHl0bTVjdXdvdjB2MEV4Q3ZAVSEpVRmdFcFJvbzFKV2VwcFFQN05nMkRQY3MZD';
 
        // //  $response = $httpClient->request('GET', "https://graph.instagram.com/v12.0/{$userId}?fields=id,username,media&access_token={$accessToken}");
 
        // // $response = $httpClient->request('GET', "https://graph.instagram.com/v18.0/{$userId}?fields=id,username,media{thumbnail_url,caption}&access_token={$accessToken}");
        // $response = $httpClient->request('GET', "https://graph.instagram.com/v18.0/{$userId}?fields=id,username,media.fields(thumbnail_url,caption)&access_token={$accessToken}");



        //  $instagramData = $response->toArray();
        //  $instagramMedia = $instagramData['media']['data'] ?? [];
         
// dump($instagramMedia); die;
// dump($response->getContent());die;
         
        return $this->render('home/index.html.twig', [
            'reservedDates' => $data,
            'categoryFirstPictures' => $categoryFirstPictures,
            'categories' => $categories,
            'reviews' => $reviews,
            'averageRating' => $averageRating,
            // 'instagramMedia' => $instagramMedia,

        ]);
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

        return $this->render('home/reviews.html.twig', [
            'reviews' => $reviews,
            'pagination' => $pagination,

        ]);
    }








}