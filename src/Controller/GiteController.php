<?php

namespace App\Controller;

use App\Entity\Gite;
use App\Entity\Period;
use App\Form\GiteType;
use App\Form\PeriodType;
use App\Repository\GiteRepository;
use App\Repository\PeriodRepository;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GiteController extends AbstractController
{

    /**
     * @var GiteRepository
     */
    private $giteRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var PictureRepository
     */
    private $pictureRepository;

    /**
     * @var PeriodRepository
     */
    private $periodRepository;


    public function __construct(GiteRepository $giteRepository, EntityManagerInterface $em, PictureRepository $pictureRepository, PeriodRepository $periodRepository)
    {
        $this->giteRepository = $giteRepository;
        $this->em = $em;
        $this->pictureRepository = $pictureRepository;
        $this->periodRepository = $periodRepository;
    }


    #[Route('/gite', name: 'app_gite')]
    public function index(): Response
    {
        $gites = $this->giteRepository->findAll();
        return $this->render('gite/index.html.twig', [
            'gites' => $gites,
        ]);
    }

    /**
    * Fonction pour ajouter ou éditer un gîte
    */

    #[Route('/gite/new', name: 'new_gite')]
    #[Route('/gite/{id}/edit', name: 'edit_gite')]

    public function new_edit(Gite $gite = null, Request $request): Response {
    
        if(!$gite) {
            $gite = new Gite();
        }
    
        $form = $this->createForm(GiteType::class, $gite);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $gite = $form->getData(); 
            // prepare en PDO
            $this->em->persist($gite);
            // execute PDO
            $this->em->flush();

            return $this->redirectToRoute('app_gite');
        }

        return $this->render('gite/new.html.twig', [
            'form' => $form,
            'edit' => $gite->getId(),
        ]);
    }  
    
    /**
    * Fonction pour supprimer un gîte
    */

    #[Route('/gite/{id}/delete', name: 'delete_gite')]
    public function delete(Gite $gite) {

        // pour préparé l'objet $gite à supprimer (enlever cet objet de la collection)
        $this->em->remove($gite);
        // flush va faire la requête SQL et concretement supprimer l'objet de la BDD
        $this->em->flush();

        return $this->redirectToRoute('app_gite');
    }





    // /**
    // * Fonction pour afficher, ajouter ou éditer une période
    // */

    #[Route('/period', name: 'app_period')]

    public function newPeriod( Request $request): Response {

        $periods = $this->periodRepository->findAll([], ['startDate' => 'ASC']);

        $newPeriod = new Period();
        
        // on recupère l'id du gite
        $gite = $this->giteRepository->find(4);

        // Associez le gîte à la période
        $newPeriod->setGite($gite);

        $form = $this->createForm(PeriodType::class, $newPeriod);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newPeriod = $form->getData(); 
  
            $startDate = $newPeriod->getStartDate();
            $endDate = $newPeriod->getEndDate();

            // Vérification des dates en BDD
            $overlappingPeriods = $this->periodRepository->findOverlappingPerriods($startDate, $endDate, $newPeriod->getId());

            if (!empty($overlappingPeriods)) {
                $this->addFlash('error', 'Les dates de début et de fin chevauchent une période existante.');
            } else {
            // Si le formulaire est valide et qu'il n'y a pas de chevauchement, enregistrez la nouvelle période
            $this->em->persist($newPeriod);
            $this->em->flush();

            $this->addFlash('success', 'La période a été ajoutée avec succès.');
            return $this->redirectToRoute('app_period');
        }
    }

        return $this->render('period/index.html.twig', [
            'form' => $form,
            'periods' => $periods
        ]);
    }  
}
