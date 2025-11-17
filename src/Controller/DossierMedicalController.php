<?php

namespace App\Controller;

use App\Entity\DossierMedical;
use App\Form\DossierMedicalType;
use App\Repository\DossierMedicalRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dossierMedical')]
final class DossierMedicalController extends AbstractController
{
    #[Route('/', name: 'app_dossier_medical')]
    public function index(): Response
    {
        return $this->render('dossier_medical/index.html.twig', [
            'controller_name' => 'DossierMedicalController',
        ]);
    }
    #[Route('/add', name: 'addForm_dossier')]
    public function addWithForm(Request $request,ManagerRegistry $doctrine)
    {
        $dossier = new DossierMedical();
        $form= $this->createForm(DossierMedicalType::class,$dossier);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em= $doctrine->getManager();
            $em->persist($dossier);
            $em->flush();
            return $this->redirectToRoute("lists_dossiers");
        }
        return $this->render("dossier_medical/add.html.twig",
            array("formDossier"=>$form));
    }

    #[Route('/list', name: 'lists_dossiers')]

    public function list(DossierMedicalRepository $repository)
    {
        $dossiers= $repository->findAll();
        return $this->render("dossier_medical/list.html.twig",
            ["tabdossiers"=>$dossiers]);
    }


    #[Route('/update/{id}', name: 'update_dossier')]
    public function update($id,DossierMedicalRepository $repository,Request $request,ManagerRegistry $doctrine)
    {
        $dossier = $repository->find($id);
        $form= $this->createForm(DossierMedicalType::class,$dossier);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em= $doctrine->getManager();
            $em->flush();
            return $this->redirectToRoute("lists_dossiers");
        }
        return $this->render("dossier_medical/update.html.twig",
            array("formDossier"=>$form));
    }

    #[Route('/remove/{id}', name: 'remove_dossier')]

    public function deleteDossier(ManagerRegistry $doctrine,$id,DossierMedicalRepository $repository)
    {
        $dossier = $repository->find($id);
        $em= $doctrine->getManager();
        $em->remove($dossier);
        $em->flush();
        return $this->redirectToRoute("lists_dossiers");

    }
}
