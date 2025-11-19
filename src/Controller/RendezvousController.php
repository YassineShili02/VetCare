<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\Rendezvous;
use App\Form\RendezvousType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class RendezvousController extends AbstractController
{
    #[Route('/rendezvous/add', name: 'rendezvous_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $rendezvous = new Rendezvous();

        $form = $this->createForm(RendezvousType::class, $rendezvous);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($rendezvous);
            $em->flush();
            return $this->redirectToRoute('rendezvous_list');
        }

        return $this->render('rendezvous/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/rendezvous/list', name: 'rendezvous_list')]
public function list(EntityManagerInterface $em): Response
{
    $rendezvous = $em->getRepository(Rendezvous::class)->findAll();

    return $this->render('rendezvous/list.html.twig', [
        'rendezvous' => $rendezvous
    ]);
}

#[Route('/rendezvous/edit/{id}', name: 'rendezvous_edit')]
public function edit(Rendezvous $r, Request $request, EntityManagerInterface $em): Response
{
    $form = $this->createForm(RendezvousType::class, $r);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();
        $this->addFlash('success', 'Rendez-vous mis à jour !');
        return $this->redirectToRoute('rendezvous_list');
    }

    return $this->render('rendezvous/edit.html.twig', [
        'form' => $form->createView(),
        'rendezvous' => $r,  // <-- Pass the entity to Twig
    ]);
}




#[Route('/rendezvous/delete/{id}', name: 'rendezvous_delete')]
public function delete($id, EntityManagerInterface $em): Response
{
    $rendezvous = $em->getRepository(Rendezvous::class)->find($id);

    if (!$rendezvous) {
        throw $this->createNotFoundException('Rendez-vous introuvable');
    }

    $em->remove($rendezvous);
    $em->flush();

    $this->addFlash('success', 'Rendez-vous supprimé !');

    return $this->redirectToRoute('rendezvous_list');
}


}
