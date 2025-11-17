<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Form\AnimalType;
use App\Repository\AnimalRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/animal")]
final class AnimalController extends AbstractController
{
    #[Route('/', name: 'app_animal')]
    public function index(): Response
    {
        return $this->render('animal/index.html.twig', [
            'controller_name' => 'AnimalController',
        ]);
    }

    #[Route('/add', name: 'addForm_animal')]
    public function addWithForm(Request $request,ManagerRegistry $doctrine)
    {
        $animal = new Animal();
        $form= $this->createForm(AnimalType::class,$animal);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em= $doctrine->getManager();
            $em->persist($animal);
            $em->flush();
            return $this->redirectToRoute("lists_animaux");
        }
        return $this->render("animal/add.html.twig",
            array("formAnimal"=>$form));
    }

    #[Route('/list', name: 'lists_animaux')]

    public function list(AnimalRepository $repository)
    {
        $animaux= $repository->findAll();
        return $this->render("animal/listAnimaux.html.twig",
            ["tabAnimaux"=>$animaux]);
    }


    #[Route('/update/{id}', name: 'update_animal')]
    public function update($id,AnimalRepository $repository,Request $request,ManagerRegistry $doctrine)
    {
        $animal = $repository->find($id);
        $form= $this->createForm(AnimalType::class,$animal);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em= $doctrine->getManager();
            $em->flush();
            return $this->redirectToRoute("lists_animaux");
        }
        return $this->render("animal/update.html.twig",
            array("formAnimal"=>$form));
    }

    #[Route('/remove/{id}', name: 'remove_animal')]

    public function deleteAnimal(ManagerRegistry $doctrine,$id,AnimalRepository $repository)
    {
        $animal = $repository->find($id);
        $em= $doctrine->getManager();
        $em->remove($animal);
        $em->flush();
        return $this->redirectToRoute("lists_animaux");

    }



}
