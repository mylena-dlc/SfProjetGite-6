<?php

namespace App\Controller;

use App\Entity\Calendar;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    #[Route('/api', name: 'app_api')]
    public function index(): Response
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
    }
    #[Route('/api/{id}/edit', name: 'api_event_edit', methods:'PUT')]
    public function majEvent(?Calendar $calendar, Request $request): Response
    {

        // on récupère les données envoyé par fullCalendar
        $donnees = json_decode($request->getContent());

        if(
            isset($donnees->title) && !empty($donnees->title) &&
            isset($donnees->start) && !empty($donnees->start) &&
            isset($donnees->end) && !empty($donnees->end) &&
            isset($donnees->description) && !empty($donnees->descrition) &&
            isset($donnees->backgroundColor) && !empty($donnees->backgroundColor) &&
            isset($donnees->borderColor) && !empty($donnees->borderColor) 
            // isset($donnees->textColor) && !empty($donnees->textColor)
        ) {
            // les données sont complètes
            // on initialise un code
            $code = 200;

            // on vérifie si l'id existe
            if(!$calendar) {
                // on instancie une reservation
                $calendar = new Calendar;

                // on change le code
                $code = 201;
            }

            // on hydrate l'objet avec nos données
            $calendar->setTitle($donnees->title);
            $calendar->setDescription($donnees->description);
            $calendar->setStart(new Datetime($donnees->start));
            $calendar->setEnd(new Datetime($donnees->end));
            $calendar->setBackgroundColor($donnees->backgroundColor);
            $calendar->setBorderColor($donnees->borderColor);
            // $calendar->setTextColor($donnees->textColor);
           
            $em = $this->getDoctrine()->getManager();
            $em->persist($calendar);
            $em->flush();

            //on retourne le code
            return new Response('Ok', $code);
        } else {
            //les données sont incomplètes
            return new Response('Données incomplètes', 404);
        }


        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
    }
} 
