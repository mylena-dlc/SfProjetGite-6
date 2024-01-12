<?php

namespace App\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mime\Email;


class ContactController extends AbstractController
{
    /**
    * Fonction pour afficher la page contact
    */

    #[Route('/contact', name: 'app_contact')]
    public function pageContact(): Response
    {
        // Affichez la vue contact
        return $this->render('contact/index.html.twig');
    }


    #[Route('/contact/email', name: 'app_contact_email')]
    public function sendEmailContact(MailerInterface $mailer, Request $request): Response
    {

        if ($request->isMethod('POST')) {
            // Récupérez les données du formulaire
            $emailFrom = $request->get('email');
            $subject = $request->get('subject');
            $messageContent = $request->get('message');

            // Envoi de mail
            $email = (new Email())
                ->from($emailFrom)
                ->to('contact@giteraindupair.fr')
                ->subject($subject)
                ->html($messageContent);
 dump($email);die;
            try {
                $mailer->send($email);
                $this->addFlash('success', 'Votre message a été envoyé avec succès. Merci pour votre demande, nous vous répondrons dans les plus brefs délais.');
                return $this->redirectToRoute('app_home');
            } catch (Exception $e) {
                $this->addFlash('error', 'Echec de l\'envoi du message. Veuillez réessayer.');
            }
        } 

    }    
}



 