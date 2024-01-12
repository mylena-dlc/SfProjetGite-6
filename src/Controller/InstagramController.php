<?php

namespace App\Controller;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InstagramController extends AbstractController
{

    // public function exchangeCodeForToken(Request $request): Response
    // {
    //     $clientId = 'votre_id_application';
    //     $clientSecret = 'votre_secret_application';
    //     $redirectUri = 'votre_uri_de_redirection';

    //     $code = $request->query->get('code'); // Le code d'autorisation reçu en paramètre

    //     $client = new Client();

    //     try {
    //         $response = $client->post('https://api.instagram.com/oauth/access_token', [
    //             'form_params' => [
    //                 '907122211128061' => $clientId,
    //                 'd2dc41f94b6d983e9321fa7d9c1e03fe' => $clientSecret,
    //                 'grant_type' => 'authorization_code',
    //                 'https://github.com/mylena-dlc' => $redirectUri,
    //                 'code' => $code,
    //             ],
    //         ]);

    //         $data = json_decode($response->getBody(), true);

    //         // À ce stade, $data contient le jeton d'accès et d'autres informations.
    //         // Vous pouvez extraire le jeton d'accès comme suit :
    //         $accessToken = $data['access_token'];

    //         // Vous pouvez utiliser $accessToken pour faire d'autres appels à l'API Instagram.

    //     } catch (\Exception $e) {
    //         // Gérez les erreurs ici
    //         return new Response('Erreur lors de l\'échange du code contre un jeton.');
    //     }

    //     // Redirigez ou effectuez d'autres actions après avoir obtenu le jeton.
    //     return $this->redirectToRoute('nom_de_votre_route');
    // }

    // #[Route('/instagram', name: 'app_instagram')]
    // public function index(): Response
    // {
    //     return $this->render('instagram/index.html.twig', [
    //         'controller_name' => 'InstagramController',
    //     ]);
    // }


    /**
     * @Route("/auth", name="auth_redirect")
     */
    #[Route('/auth', name: 'app_redirect')]

    public function redirectToInstagramAuth(): Response
    {
        $clientId = '907122211128061';
        $redirectUri = 'https://github.com/mylena-dlc';
        $scope = 'user_profile,user_media';
        $responseType = 'code';

        $authUrl = sprintf(
            'https://api.instagram.com/oauth/authorize?client_id=%s&redirect_uri=%s&scope=%s&response_type=%s',
            $clientId,
            $redirectUri,
            $scope,
            $responseType
        );

        return new RedirectResponse($authUrl);
        // return $this->redirectToRoute('app_register');
        // return $this->render('registration/register.html.twig', [
        //     'registrationForm' => $form->createView(),
        // ]);
        }



      #[Route('/insta', name: 'app_insta')]
    public function index(Session $session): Response
    {
        // Récupérez le jeton d'accès depuis la session
    $accessToken = $session->get('access_token');

        return $this->render('instagram/index.html.twig', []);
    }
}

//                 #[Route('/token', name: 'app_redirect')]
//                 public function obtenirAccessToken(Session $session)
//                 {
//                     $clientId = '';
//                     $clientSecret = '';
//                     $redirectUri = '';
                    
//                     curl -X POST \
//                     https://api.instagram.com/oauth/access_token \
//                     -F client_id=907122211128061
//                     -F client_secret=d2dc41f94b6d983e9321fa7d9c1e03fe \
//                     -F grant_type=authorization_code \
//                     -F redirect_uri=https://github.com/mylena-dlc \
//                     -F code=AQBgiSvA9qfhSN5ntEGV2e6OnjGD0g7ZitfdSOdjeLh0TJeFsNGMFT6P55CGuGOuwtnBJkQQKMYSEoxwVNRkW-h_ylAH2QpQmIdqcQOpLZv7fiXyDyZ75tFDadxEj-sa4UlrR9B-aCNzL345wFn74Wzb_0Bvebqq-IBUDbNivtQZiJRpfcy2EdG3yXuIN_qqXl4PfrllttCqJMBIWNiDnDFnQOAvltf-zYlMFcPQl1TewQ



//                     $urlToken = 'https://api.instagram.com/oauth/access_token';
                
//                     $httpClient = HttpClient::create();
                
//                     $response = $httpClient->request('POST', $urlToken, [
//                         'body' => [
//                             'client_id' => $clientId,
//                             'client_secret' => $clientSecret,
//                             'grant_type' => 'authorization_code',
//                             'redirect_uri' => $redirectUri,
//                             'code' => $code,
//                         ],
//                     ]);
                
//                     $donnees = $response->toArray();
                
//                     if (isset($donnees['access_token'], $donnees['user_id'])) {
//                         // Le jeton d'accès et l'ID utilisateur sont dans $donnees['access_token'] et $donnees['user_id']
//                         $accessToken = $donnees['access_token'];
//                         $userId = $donnees['user_id'];
                
//                             // Stockez le jeton d'accès dans la session
//         $session->set('access_token', $accessToken);

//         // Affichez le jeton d'accès dans la console Symfony
//         dump($accessToken);

//                         // Redirigez l'utilisateur vers une autre page de votre application
//                         return $this->redirectToRoute('app_insta');
//                     }

//                 if ($response->getStatusCode() !== 200) {
//     dump($response->toArray());
//                     // Gérer les erreurs ici si la demande n'a pas réussi.
//                     // Vous pouvez également rediriger vers une autre page d'erreur si nécessaire.
//                     // return $this->redirectToRoute('nom_de_votre_page_d_erreur');
//                 }
                
// }
// }