<?php

namespace App\Controller;

use DateInterval;
use Stripe\Stripe;
use App\Entity\Reservation;
use Stripe\Checkout\Session;
use App\Form\ReservationType;
use App\Service\DompdfService;
use App\Service\SendMailService;
use App\Repository\GiteRepository;
use App\Repository\UserRepository;
use App\Repository\PeriodRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ReservationController extends AbstractController
{

    /**
     * @var ReservationRepository
     */
    private $reservationRepository;

    /**
     * @var GiteRepository
     */
    private $giteRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var PeriodRepository
     */
    private $periodRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    protected $stripeSecretKey;
    protected $stripePublishableKey;


    
    public function __construct(ReservationRepository $reservationRepository, GiteRepository $giteRepository, EntityManagerInterface $em, UserRepository $userRepository, PeriodRepository $periodRepository, string $stripeSecretKey, string $stripePublishableKey )
    {
        $this->reservationRepository = $reservationRepository;
        $this->giteRepository = $giteRepository;
        $this->userRepository = $userRepository;
        $this->periodRepository = $periodRepository;
        $this->em = $em;
        $this->stripeSecretKey = $stripeSecretKey;
        $this->stripePublishableKey = $stripePublishableKey;
    }
    
    #[Route('/reservation', name: 'app_reservation')]
    public function index(Request $request): Response
    {
        if ($request->isMethod('POST')) {

            // Récupérez les données du formulaire
            $startDate = $request->get('start');
            $endDate = $request->get('end');
            $dateRange = $request->get('start');

            // Si les dates ne sont pas sélectionnées, redirection et message d'erreur
            if(!$startDate) {
                $this->addFlash('error', 'Vous devez sélectionner vos dates.');
                return $this->redirectToRoute('app_home');  
            } 

            // Extraire les dates de début et de fin
            list($startDate, $endDate) = explode(' to ', $dateRange);

            // Récupérez les dates de début et de fin de la réservation
            $startDate = new \DateTime($startDate);
            $endDate = new \DateTime($endDate);
            
            $numberAdult = $request->get('numberAdult');
            $numberKid = $request->get('numberKid');    
            

            // Vérifiez si les dates sélectionnées chevauchent d'autres réservations en BDD
            $overlappingReservations = $this->reservationRepository->findOverlappingReservations($startDate, $endDate);

            // S'il y a des chevauchements, retournez à la page d'accueil avec une alerte
            if (!empty($overlappingReservations)) {
                $this->addFlash('error', 'Les dates choisies ne sont plus disponibles.');
                return $this->redirectToRoute('app_home');  
            }

            // Vérifiez si les dates de la réservation chevauchent une période avec supplément
            $overlappingPeriods = $this->periodRepository->findOverlappingPerriods($startDate, $endDate);

            // Initialisez le supplément à zéro par défaut
            $supplement = 0;

            if (!empty($overlappingPeriods)) {
                // Ajoutez le supplément au prix de la réservation
                foreach ($overlappingPeriods as $period) {
                    $supplement += $period->getSupplement(); 
                }
            }

            // on recupère l'id du gite
            $gite = $this->giteRepository->find(4);

            // on recherche le prix du forfait ménage
            $cleaningCharge = $gite->getCleaningCharge();
            // on recherche le prix de la nuit
            $nightPrice = $gite->getPrice() + $supplement;

            // on compte le nombre de nuit
            $diff = $startDate->diff($endDate);
            $numberNight = $diff->format('%a');

            // Calculez le prix total de la réservation (avec supplément)
            $totalPrice = ($numberNight * $nightPrice) + $cleaningCharge + $supplement;

            // Stockez les données dans la session
            $session = $request->getSession();
            $session->set('reservation_details', [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'numberAdult' => $numberAdult,
                'numberKid' => $numberKid,
                'numberNight' => $numberNight,
                'nightPrice' => $nightPrice,
                'cleaningCharge' => $cleaningCharge,
                'supplement' => $supplement,
                'totalPrice' => $totalPrice,
            ]);
        }

        $description = 'Planifiez votre séjour dans notre gîte de charme à Orbey en Alsace. Consultez les disponibilités, tarifs, et réservez vos dates en quelques clics. Profitez d\'une escapade inoubliable !';
        
        return $this->render('reservation/index.html.twig', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'numberAdult' => $numberAdult,
            'numberKid' => $numberKid,
            'numberNight' => $numberNight,
            'nightPrice' => $nightPrice,
            'cleaningCharge' => $cleaningCharge,
            'supplement' => $supplement,
            'totalPrice' => $totalPrice,
            'description' => $description
        ]);
    }

    

    /**
    * Fonction pour confirmer une réservation
    */

    #[Route('/reservation/new', name: 'new_reservation')]
    public function new(Reservation $reservation = null, Request $request, Security $security): Response {
    

    // Vérifiez si l'utilisateur est connecté
    if (!$security->isGranted('IS_AUTHENTICATED_FULLY')) {
        // Redirigez l'utilisateur vers la page de connexion
        return $this->redirectToRoute('app_login');
    }

    // Récupérez les données stockées en session
    $session = $request->getSession();

    $arrivalDate = $session->get('reservation_details')['startDate'];
    $departureDate = $session->get('reservation_details')['endDate'];
    $numberAdult = $session->get('reservation_details')['numberAdult'];
    $numberKid = $session->get('reservation_details')['numberKid'];
    $numberNight = $session->get('reservation_details')['numberNight'];
    $nightPrice = $session->get('reservation_details')['nightPrice'];
    $totalPrice = $session->get('reservation_details')['totalPrice'];

    // Créez une instance de l'entité Reservation et définissez les données initiales
    $reservation = new Reservation();

    $reservation->setArrivalDate($arrivalDate); 
    $reservation->setDepartureDate($departureDate);
    $reservation->setNumberAdult($numberAdult);
    $reservation->setNumberKid($numberKid);
    $reservation->setTotalPrice($totalPrice);

    // on recupère l'id du gite
    $gite = $this->giteRepository->find(4);

    // Associez le gîte à la réservation
    $reservation->setGite($gite);

    // on récupère l'id de l'utilisateur connecté et son email
    $user = $this->getUser();
    $email = $user->getEmail();

    $reservation->setUser($user);
    $reservation->setEmail($email);

    $form = $this->createForm(ReservationType::class, $reservation);

    // Gérez la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $reservation = $form->getData(); 

            // prepare en PDO
            $this->em->persist($reservation);
            // execute PDO
            $this->em->flush();

            return $this->redirectToRoute('paiement', ['id' => $reservation->getId()]);
        }

        $description = 'Validez votre réservation pour notre gîte à Orbey. Vérifiez les détails, les tarifs, et complétez vos coordonnées en toute sécurité. Séjournez dans notre charmant hébergement en Alsace.';
        
        // Affichez le formulaire dans la vue Twig
        return $this->render('reservation/new.html.twig', [
            'form' => $form,
            'arrivalDate' => $arrivalDate->format('d-m-Y'),
            'departureDate' => $departureDate->format('d-m-Y'),
            'numberAdult' => $numberAdult,
            'numberKid' => $numberKid,
            'numberNight' => $numberNight,
            'nightPrice' => $nightPrice,
            'totalPrice' => $totalPrice,
            'description' => $description
        ]);
    }   


    /**
    * Fonction de paiement Stripe
    */

    #[Route('/reservation/{id}/paiement', name: 'paiement')]
    public function paiement( int $id, Request $request): Response {

    $reservation = $this->reservationRepository->findOneBy(['id' => $id]);

    // Récupérez les détails de la réservation
    $totalPrice = $reservation->getTotalPrice() * 100; // Conversion du prix en centimes
 
    // Configurez Stripe
    Stripe::setApiKey($this->stripeSecretKey);
    
    // Créez une session de paiement avec Stripe Checkout
    $session = Session::create([
        'mode' => ['payment'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => 'Réservation de gîte',
                ],
                'unit_amount' => $totalPrice,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => $this->generateUrl('confirm_reservation', ['id' => $reservation->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
        'cancel_url' => $this->generateUrl('payment_error', ['id' => $reservation->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
    ]);

        // Redirigez l'utilisateur vers la page de paiement de Stripe
        return $this->redirect($session->url);
}



    /**
    * Fonction pour afficher la vue de confirmation d'une réservation
    */

    #[Route('/reservation/{id}/confirm', name: 'confirm_reservation')]
    public function confirm(int $id, Request $request, SendMailService $mail, DompdfService $dompdfService): Response {

        $reservation = $this->reservationRepository->findOneBy(['id' => $id]);
        $gite = $this->giteRepository->find(4);

        // Obtenez les dates de début et de fin de la réservation
        $startDate = $reservation->getArrivalDate();
        $endDate = $reservation->getDepartureDate();

        // Calcule du nombre de nuit
        $diff = $startDate->diff($endDate);
        $numberNight = $diff->format('%a');

        // Calcul du prix sans le forfait de ménage
        $cleaningCharge = $gite->getcleaningCharge();
        $priceHt = $reservation->getTotalPrice() - $cleaningCharge;

        // Récupérer le contenu du template de la facture
        $invoiceContent = $this->renderView('reservation/invoice.html.twig', [
        'reservation' => $reservation,
        'numberNight' => $numberNight,
        'gite' => $gite,
        'priceHt' => $priceHt,
        'logo' => $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/img/logook2.png'),
    ]);

        // Générez le PDF à partir du HTML
        $pdfContent = $dompdfService->generatePdf($invoiceContent);

        // Convertir le contenu du PDF en une chaîne Base64
        $pdfBase64 = base64_encode($pdfContent);

        // Envoyer le mail de confirmation
        $mail->send(
            'contact@giteraindupair.fr',
            $reservation->getEmail(), 
            'Confirmation de réservation',
            'confirm-reservation',
            [
                'reservation' => $reservation,
                'pdfBase64' => $pdfBase64, 
                'logo' => $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/img/logook2.png'),
            ],
        );

        // Envoyer un e-mail à l'administrateur
        $mail->sendAdminNotification(
            'contact@giteraindupair.fr',
            'admin@giteraindupair.com',
            'Nouvelle réservation',
            'admin-notification',
            [
                'reservation' => $reservation,
            ],
        );

        $description = 'Votre réservation dans notre gîte de charme à Orbey en Alsace est confirmée. Préparez-vous à vivre une expérience exceptionnelle dans notre maison de vacances!';
    
        return $this->render('reservation/confirm.html.twig', [
            'description' => $description
        ]);
}


    /**
    * Fonction pour afficher une page d'erreur si le paiement échoue
    */

    #[Route('/reservation/{id}/error', name: 'payment_error')]
    public function stripeError(int $id, SessionInterface $session)
    {
        // Recherche de la réservation en cours
        $reservation = $this->reservationRepository->findOneBy(['id' => $id]);
        
        // Suppression de la réservation en BDD
        $this->em->remove($reservation);
        $this->em->flush();
        
        // Suppression de la session
        $session->remove('reservation_details');

        $this->addFlash('error', 'Le paiement a échoué, veuilliez recommencer votre réservation.');
        return $this->redirectToRoute('app_home');
    }


    /**
    * Fonction pour afficher les détails d'une réservation
    */

    #[Route('/reservation/{id}', name: 'show_reservation')]
    public function show(int $id): Response
    {
    // Récupérez la réservation depuis la base de données
    $reservation = $this->reservationRepository->find($id);

    $description = 'Détails de votre séjour passé dans notre gîte à Orbey. Retrouvez les dates, tarifs, et toutes les informations liées à votre réservation. Revivez vos moments de vacances en Alsace.';

    // Passez les données de la réservation à la vue
    return $this->render('reservation/show.html.twig', [
        'reservation' => $reservation,
        'description' => $description
    ]);
}


    /**
    * Fonction pour télécharger la facture d'une réservation
    */

    #[Route('/reservation/{id}/show/download-invoice', name: 'download_invoice')]
    public function downloadInvoice(DompdfService $dompdfService, int $id): Response
    {
        // Récupérez les informations nécessaires pour la facture
        $reservation = $this->reservationRepository->find($id);
        $gite = $this->giteRepository->find(4);

         // Obtenez les dates de début et de fin de la réservation
        $startDate = $reservation->getArrivalDate();
        $endDate = $reservation->getDepartureDate();

        // Calcule du nombre de nuit
        $diff = $startDate->diff($endDate);
        $numberNight = $diff->format('%a');

        // Calcul du prix sans le forfait de ménage
        $cleaningCharge = $gite->getcleaningCharge();
        $priceHt = $reservation->getTotalPrice() - $cleaningCharge;


        // Générez le HTML pour la facture
        $html = $this->renderView('reservation/invoice.html.twig', [
            'reservation' => $reservation, 
            'numberNight' => $numberNight,
            'gite' => $gite,
            'priceHt' => $priceHt,
            'logo' => $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/img/logook2.png'),
        ]);

        // Générez le PDF à partir du HTML
        $pdfContent = $dompdfService->generatePdf($html);

        // Créez une réponse de téléchargement
        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment;filename=facture.pdf');
        return $response;
    }

    // Fonction pour encoder le logo

    private function imageToBase64($path) {
        $path = $path;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }

}


