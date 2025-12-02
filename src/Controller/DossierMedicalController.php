<?php

namespace App\Controller;

use App\Entity\DossierMedical;
use App\Entity\Animal;
use App\Form\DossierMedicalType;
use App\Repository\DossierMedicalRepository;
use App\Repository\AnimalRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/dossiermedical')]
class DossierMedicalController extends AbstractController
{
    private string $dossierMedicalImagesDir;

    public function __construct(string $dossierMedicalImagesDir)
    {
        $this->dossierMedicalImagesDir = $dossierMedicalImagesDir;
    }

    #[Route('/', name: 'app_dossier_medical')]
    public function index(): Response
    {
        return $this->render('dossier_medical/index.html.twig');
    }

    // 🟢 Ajout d'un dossier pour un animal spécifique
    #[Route('/add/{animalId}', name: 'add_dossier_for_animal')]
    public function addForAnimal(
        int $animalId,
        AnimalRepository $animalRepository,
        DossierMedicalRepository $dossierRepository,
        Request $request,
        ManagerRegistry $doctrine
    ): Response {
        $animal = $animalRepository->find($animalId);
        if (!$animal) {
            throw $this->createNotFoundException('Animal introuvable');
        }

        // Prevent duplicate dossiers
        if ($animal->getDossierAnimal()) {
            $this->addFlash('warning', 'Cet animal a déjà un dossier médical.');
            return $this->redirectToRoute('lists_dossiers');
        }

        $dossier = new DossierMedical();
        $dossier->setAnimal($animal);

        $form = $this->createForm(DossierMedicalType::class, $dossier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $dossier->setNumeroDossier('D-' . uniqid());
            $dossier->setDateCreation(new \DateTime());

            // 🔥 Récupérer les images du formulaire
            $images = $form->get('images')->getData();

            if ($images) {
                $imageNames = [];
                foreach ($images as $image) {
                    $newFilename = uniqid() . '.' . $image->guessExtension();
                    $image->move($this->getParameter('dossier_medical_images_dir'), $newFilename);
                    $imageNames[] = $newFilename;
                }
                $dossier->setImages($imageNames);
            }

            $em = $doctrine->getManager();
            $em->persist($dossier);
            $em->flush();

            $this->addFlash('success', 'Dossier médical ajouté avec succès !');
            return $this->redirectToRoute('lists_dossiers');
        }


        return $this->render('dossier_medical/add.html.twig', [
            'formDossier' => $form->createView(),
            'animal' => $animal,
        ]);
    }

    #[Route('/add', name: 'addForm_dossier')]
    public function addWithForm(Request $request, ManagerRegistry $doctrine)
    {
        $dossier = new DossierMedical();
        $form = $this->createForm(DossierMedicalType::class, $dossier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if the selected animal already has a dossier
            $animal = $dossier->getAnimal();
            if ($animal && $animal->getDossierAnimal()) {
                $this->addFlash('warning', 'Cet animal a déjà un dossier médical.');
                return $this->redirectToRoute('lists_dossiers');
            }

            $dossier->setNumeroDossier('D-' . uniqid());
            $dossier->setDateCreation(new \DateTime());

            $images = $form->get('images')->getData();
            if ($images) {
                $imageNames = [];
                foreach ($images as $image) {
                    $newFilename = uniqid() . '.' . $image->guessExtension();
                    $image->move($this->getParameter('dossier_medical_images_dir'), $newFilename);
                    $imageNames[] = $newFilename;
                }
                $dossier->setImages($imageNames);
            }

            $em = $doctrine->getManager();
            $em->persist($dossier);
            $em->flush();

            $this->addFlash('success', 'Dossier médical ajouté avec succès !');
            return $this->redirectToRoute('lists_dossiers');
        }

        return $this->render('dossier_medical/add.html.twig', [
            'formDossier' => $form->createView(),
        ]);
    }


    #[Route('/list', name: 'lists_dossiers')]
    public function list(DossierMedicalRepository $repository): Response
    {
        $dossiers = $repository->findAllOrderByNewest();

        return $this->render('dossier_medical/list.html.twig', [
            'tabdossiers' => $dossiers,
        ]);
    }
    #[Route('/dossier/show/{id}', name: 'show_dossier')]
    public function show(DossierMedical $dossier): Response
    {
        return $this->render('dossier_medical/show_dossier.html.twig', [
            'dossier' => $dossier
        ]);
    }


    #[Route('/update/{id}', name: 'update_dossier')]
    public function update(
        int $id,
        DossierMedicalRepository $repository,
        Request $request,
        ManagerRegistry $doctrine
    ): Response {
        $dossier = $repository->find($id);
        if (!$dossier) {
            throw $this->createNotFoundException('Dossier médical introuvable');
        }

        $form = $this->createForm(DossierMedicalType::class, $dossier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form->get('images')->getData();
            if ($images) {
                $imageNames = $dossier->getImages() ?? [];
                foreach ($images as $image) {
                    $newFilename = uniqid() . '.' . $image->guessExtension();
                    try {
                        $image->move($this->dossierMedicalImagesDir, $newFilename);
                        $imageNames[] = $newFilename;
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                    }
                }
                $dossier->setImages($imageNames);
            }

            $doctrine->getManager()->flush();
            $this->addFlash('success', 'Dossier médical modifié avec succès !');
            return $this->redirectToRoute('lists_dossiers');
        }

        return $this->render('dossier_medical/update.html.twig', [
            'formDossier' => $form->createView(),
            'dossier' => $dossier,
        ]);
    }

    #[Route('/remove/{id}', name: 'remove_dossier')]
    public function remove(
        int $id,
        DossierMedicalRepository $repository,
        ManagerRegistry $doctrine
    ): Response {
        $dossier = $repository->find($id);
        if (!$dossier) {
            throw $this->createNotFoundException('Dossier médical introuvable');
        }

        $em = $doctrine->getManager();
        $em->remove($dossier);
        $em->flush();

        $this->addFlash('success', 'Dossier médical supprimé avec succès !');
        return $this->redirectToRoute('lists_dossiers');
    }
}
