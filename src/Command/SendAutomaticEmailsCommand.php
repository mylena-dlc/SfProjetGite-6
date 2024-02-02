<?php

namespace App\Command;

use App\Service\SendMailService;
use App\Repository\ReservationRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'SendAutomaticEmails',
    description: 'Envoi automatique de mail',
)]
class SendAutomaticEmailsCommand extends Command
{
    private $sendMailService;
    private $reservationRepository;
    private $parameterBag;


    public function __construct(SendMailService $sendMailService, ReservationRepository $reservationRepository, ParameterBagInterface $parameterBag)
    {
        $this->sendMailService = $sendMailService;
        $this->reservationRepository = $reservationRepository;
        $this->parameterBag = $parameterBag;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Send automatic emails for reservations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
       // Logique pour récupérer les réservations et envoyer les e-mails
       $reservations = $this->reservationRepository->getReservationToSendMail();


       foreach ($reservations as $reservation) {
      
        // Générer l'URL pour écrire un avis
        $reviewLink = "http://127.0.0.1:8000/security/writeReview".$reservation->getUser()->getId()."/".$reservation->getId();
               // Envoyer l'e-mail automatique
               $this->sendMailService->send(
                   'ne-pas-repondre@giteraindupair.com',
                   $reservation->getEmail(),
                   'Merci pour votre séjour au Gîte du Rain du Pair',
                   'email-automatic-review',
                   ['reservation' => $reservation,
                    'reviewLink' => $reviewLink,
                    'logo' => $this->imageToBase64($this->parameterBag->get('kernel.project_dir') . '/public/img/logook2.png'),
                    ]
               );
       }

       $io->success('Automatic emails sent successfully.');

       return Command::SUCCESS;
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