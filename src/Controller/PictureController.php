<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Form\PictureType;
use App\Repository\GiteRepository;
use App\Repository\PictureRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

class PictureController extends AbstractController
{

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var PictureRepository
     */
    private $pictureRepository;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var GiteRepository
     */
    private $giteRepository;

    public function __construct(PictureRepository $pictureRepository, EntityManagerInterface $em, CategoryRepository $categoryRepository, GiteRepository $giteRepository)
    {
        $this->pictureRepository = $pictureRepository;
        $this->em = $em;
        $this->categoryRepository = $categoryRepository;
        $this->giteRepository = $giteRepository;
    }

    #[Route('/picture', name: 'app_picture')]
    public function index(): Response
    {
        $pictures = $this->pictureRepository->findBy([], []);

        return $this->render('picture/index.html.twig', [
            'pictures' => $pictures,
        ]);
    }

    /**
    * Fonction pour ajouter une photo
    */

    #[Route('/category/newpicture', name: 'new_picture')]
    public function newPicture(Picture $picture = null, Request $request): Response {
       
        // on crée une nouvelle instance
        $picture = new Picture();
        
        // on crée un formulaire en utilisant la classe pictureType et associe la picture à ce formulaire
        $form = $this->createForm(PictureType::class, $picture);

        // on cherche l'id de la catégorie
        // $category = $this->categoryRepository->find($category_id);

        // on recupère l'id du gite
        $gite = $this->giteRepository->find(4);

        // Associez le gîte à la réservation
        $picture->setGite($gite);

        // la méthode handleRequest traite les données du formulaire
        $form->handleRequest($request);

        // on vérifie si le formulaire a été soumis et s'il est valide
        if($form->isSubmitted() && $form->isValid()) {

            if ($form->get('category')->getData()) {
                $category = $form->get('category')->getData();
                $picture->setCategory($category);
            }

            $pictureFile = $form->get('picture')->getData();

            if ($pictureFile) {
                $newFilename = uniqid().'.'.$pictureFile->guessExtension();
                $newFilePath = $this->getParameter('pictures_directory').'/'.$newFilename;

                try {
                    $pictureFile->move(
                        $this->getParameter('pictures_directory'),
                        $newFilename
                    );                
                    
                    $picture->setUrl('uploads/' . $newFilename);

                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement du fichier.');
                } catch (AccessDeniedException $e) {
                    $this->addFlash('error', 'Accès refusé au répertoire de stockage des images.');
                }
            }

            // persiste la picture en BDD (enregistrer les données de l'objet picture en BDD, ajouter ou mettre à jour):
            $this->em->persist($picture); // indique à Doctrine de suivre cette instance de picture pour une eventuelle opération de persistance
            $this->em->flush(); // envoie réellement les opérations en BDD

            // Ajoutez un message flash de succès
            if ($picture->getId()) {
                $this->addFlash('success', 'L\'image a été ajoutée.');
            } else {
                $this->addFlash('success', 'Echec lors de l\'ajout de l\'image.');
            }

            return $this->redirectToRoute('app_category'); 
        }

        // affiche la vue pour le formulaire d'ajout ou d'édition
        return $this->render('picture/new.html.twig', [ // fonction render() génère une page HTML à partir du modèle template. l'argument est un tableau associatif qui permet de passer des données au template pour les afficher
            'form' => $form, // transmet le formulaire
            'edit' => $picture->getId(), // transmet l'ID de la picture actuelle (ajout ou edit)
            'pictureId' => $picture->getId() // transmet aussi l'ID de la photo mais sous un autre nom
        ]);
    }   


    /**
    * Fonction pour modifier une photo dans une catégorie d'image
    */

    #[Route('/{category_id}/picture/{id}/edit', name: 'edit_picture')]

    public function edit(Picture $picture = null, Request $request, $category_id): Response {
       
        // on crée une nouvelle instance
        if(!$picture) {
            $picture = new Picture();
        }
        
        // on crée un formulaire en utilisant la classe pictureType et associe la picture à ce formulaire
        $form = $this->createForm(PictureType::class, $picture);

        // on cherche l'id de la catégorie
        $category = $this->categoryRepository->find($category_id);

        // la méthode handleRequest traite les données du formulaire
        $form->handleRequest($request);

        // on vérifie si le formulaire a été soumis et s'il est valide
        if($form->isSubmitted() && $form->isValid()) {

            $pictureFile = $form->get('picture')->getData();

            if ($pictureFile) {
                $newFilename = uniqid().'.'.$pictureFile->guessExtension();
                $newFilePath = $this->getParameter('pictures_directory').'/'.$newFilename;

                try {
                    $pictureFile->move(
                        $this->getParameter('pictures_directory'),
                        $newFilename
                    );                
                    
                    $picture->setUrl('uploads/' . $newFilename);

                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement du fichier.');
                } catch (AccessDeniedException $e) {
                    $this->addFlash('error', 'Accès refusé au répertoire de stockage des images.');
                }
            }

            $picture->setCategory($category); // on ajoute l'id de la catégorie à picture
            
            // persiste la picture en BDD (enregistrer les données de l'objet picture en BDD, ajouter ou mettre à jour):
            $this->em->persist($picture); // indique à Doctrine de suivre cette instance de picture pour une eventuelle opération de persistance
            $this->em->flush(); // envoie réellement les opérations en BDD

            // Ajoutez un message flash de succès
            if ($picture->getId()) {
                $this->addFlash('success', 'L\'image a été modifiée.');
            } else {
                $this->addFlash('error', 'Echec lors de la modification de l\'image.');
            }

            return $this->redirectToRoute('show_category' , ['id' => $category_id]); // redirection vers la page
        }

        // affiche la vue pour le formulaire d'ajout ou d'édition
        return $this->render('picture/new.html.twig', [ // fonction render() génère une page HTML à partir du modèle template. l'argument est un tableau associatif qui permet de passer des données au template pour les afficher
            'form' => $form, // transmet le formulaire
            'edit' => $picture->getId(), // transmet l'ID de la picture actuelle (ajout ou edit)
            'pictureId' => $picture->getId() // transmet aussi l'ID de la photo mais sous un autre nom
        ]);
    }   
}
