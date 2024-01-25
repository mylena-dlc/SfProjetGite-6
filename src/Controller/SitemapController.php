<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SitemapController extends AbstractController
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }


    #[Route('/sitemap.xml', name: 'app_sitemap', defaults: ['_format' => 'xml'])]
    public function index(Request $request): Response
    {
        // On récupère le nom d'hôte depuis l'URL
        $hostname = $request->getSchemeAndHttpHost();

        // On initialise un tableau pour lister les URLs
        $urls = [];

        // On ajoute les URLs "statiques"
        $urls[] = ['loc' => $this->generateUrl('app_home')];
        $urls[] = ['loc' => $this->generateUrl('app_login')];
        $urls[] = ['loc' => $this->generateUrl('app_register')];
        $urls[] = ['loc' => $this->generateUrl('app_all_reviews')];
        $urls[] = ['loc' => $this->generateUrl('app_contact')];

        // dump($urls); die;
        // On ajoute les URLs "dynamiques"
        // $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
        $categories = $this->categoryRepository->findAll();


        foreach ($categories as $category) {
            $categoryUrl[] = ['loc' => $this->generateUrl('app_category', [
                'name' => $category->getName()
                ]
            )];

            $urls[] = [
                'loc' => $categoryUrl,
                'pictures' => $category->getPictures()
            ];

            foreach ($category->getPictures() as $picture) {
                $urls[] = [
                    'loc' => $this->generateUrl('show_category', ['id' => $picture->getId()]),
                    'picture' => $picture
                ];
            }
        }

        // Fabrication de la réponse
        $response = new Response(
            $this->renderView('sitemap/index.html.twig', [
                'urls' => $urls,
                'hostname' => $hostname
            ])
        );
        
        // Ajout des entêtes HTTP
        $response->headers->set('Content-Type', 'text/xml');

        // On envoie la réponse
        return $response;
    }
}
