<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JWTService;
use App\Security\EmailVerifier;
use App\Service\SendMailService;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\AppAuthenticator;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppAuthenticator $authenticator, EntityManagerInterface $entityManager, SendMailService $mail, JWTService $jwt): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // on recupère le champ 'Honeypot'
            $honeypot = $form->get('honeypot')->getData();
            // si le champs pot de miel est rempli, cela indique une soumission automatisé
            if(!empty($honeypot)) { 
                // on rejete le formulaire et on ajoute une erreur au champ
                $form->get('honeypot')->addError(new FormError('Ce champs doit être vide.'));
                $this->addFlash('error', "Erreur lors de l'inscription !");
                
                return $this->redirectToRoute('app_register');

            } else {

            // Hachage du mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // Générer le JWT de l'utilisateur
            // Header
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];

            // Payload
            $payload = [
                'user_id' => $user->getId()
            ];

            // Générer le token
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            // Encodage du logo
            $logo = $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/img/logook2.png');

            // Envoi du mail de vérification de compte
            $mail->send(
                'no-reply@giteraindupair.fr',
                $user->getEmail(),
                'Activation de votre compte sur le site du Gîte du Rain du Pair',
                'register',
                compact('user', 'token', 'logo')
            );

            $this->addFlash('success', 'Votre compte a été créé avec succès. Veuillez valider votre compte en cliquant sur le lien envoyé à votre adresse e-mail afin de pouvoir réserver votre séjour.');
            return $this->redirectToRoute('app_login');
        }
    }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }



    // Envoi du lien d'activation du compte

    #[Route('/verif/{token}', name: 'verify_user')]
    public function verifyUser($token, JWTService $jwt, UserRepository $userRepository, EntityManagerInterface $em): Response
    {            
        
        // On vérifie si le token est valide, n'a pas expiré et n'a pas été modifié
        if($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))) {
            
            // On récupère le payload
            $payload = $jwt->getPayload($token);

            // On récupère le user du token
            $user = $userRepository->find($payload['user_id']);

            // On vérifie que l'user existe et n'a pas encore activé son compte
            if($user && !$user->getIsVerified()) {
                $user->setIsVerified(true);
                $em->flush($user);

                $this->addFlash('success', 'Votre compte a bien été activé !');
                return $this->redirectToRoute('app_login');
            }
        }

        // S'il y a un probleme avec le token
        $this->addFlash('error', 'Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }



    // Fonction pour renvoyer le lien d'activation

    #[Route('/renvoiverif', name: 'resend_verif')]
    public function resendVerif(JWTService $jwt, UserRepository $userRepository, SendMailService $mail): Response
    {
        $user = $this->getUser();
        if(!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }
        if($user->getIsVerified()) {
            $this->addFlash('error', 'Le compte est déjà activé !');
            return $this->redirectToRoute('app_login');
        }

         // Générer le JWT de l'utilisateur
            // Header
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];

            // Payload
            $payload = [
                'user_id' => $user->getId()
            ];

            // Générer le token
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            // Encodage du logo
            $logo = $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/img/logook2.png');

            // Envoi du mail de vérification de compte
            $mail->send(
                'ne-pas-repondre@giteraindupair.fr',
                $user->getEmail(),
                'Activation de votre compte sur le site du Gîte du Rain du Pair',
                'register',
                compact('user', 'token', 'logo')
            );

            $this->addFlash('success', 'Lien de vérification envoyé sur votre boîte mail ! Vous avez 3 heures pour le valider.');
            return $this->redirectToRoute('app_profil', ['id' => $user->getId()]);
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
