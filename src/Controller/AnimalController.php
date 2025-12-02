<?php

    namespace App\Controller;

    use App\Entity\Animal;
    use App\Form\AnimalType;
    use App\Repository\AnimalRepository;
    use Doctrine\Persistence\ManagerRegistry;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;

    #[Route('/animal')]
    final class AnimalController extends AbstractController
    {
        #[Route('/', name: 'home')]
        public function index(AnimalRepository $animalRepository): Response
        {
            // Get all animals from database
            $animals = $animalRepository->findAll();

            // Render template and pass the variable
            return $this->render('animal/index.html.twig', [
                'animals' => $animals
            ]);
        }

        #[Route('/add', name: 'addForm_animal')]
        public function addWithForm(Request $request, ManagerRegistry $doctrine)
        {
            $animal = new Animal();

            $form = $this->createForm(AnimalType::class, $animal);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em = $doctrine->getManager();
                $em->persist($animal);
                $em->flush();

                $this->addFlash('success', 'Animal ajouté avec succès !');
                return $this->redirectToRoute('lists_animaux');
            }

            return $this->render('animal/add.html.twig', [
                'formAnimal' => $form->createView(),
            ]);
        }




        #[Route('/list', name: 'lists_animaux')]
        public function list(Request $request,  AnimalRepository $repository ): Response
        {
            $sort = $request->query->get('sort', 'date_enregistrement'); // défaut : date enreg.
            $order = $request->query->get('order', 'DESC'); // défaut : du plus récent au plus ancien

            $animaux = $repository->findAllSorted($sort, $order);

            return $this->render('animal/listAnimaux.html.twig', [
                'tabAnimaux' => $animaux,
                'sort' => $sort,
                'order' => $order,
            ]);
        }

        #[Route('/update/{id}', name: 'update_animal')]
        public function update(
            int $id,
            AnimalRepository $repository,
            Request $request,
            ManagerRegistry $doctrine
        ) {
            $animal = $repository->find($id);

            if (!$animal) {
                throw $this->createNotFoundException('Animal not found');
            }

            $form = $this->createForm(AnimalType::class, $animal);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em = $doctrine->getManager();

                $em->flush();

                return $this->redirectToRoute('lists_animaux');
            }

            return $this->render('animal/update.html.twig', [
                'formAnimal' => $form->createView(),
            ]);
        }

        #[Route('/remove/{id_animal}', name: 'remove_animal')]
        public function deleteAnimal(
            ManagerRegistry $doctrine,
            int $id_animal,
            AnimalRepository $repository
        ) {
            $animal = $repository->find($id_animal);

            if (!$animal) {
                $this->addFlash('error', 'Animal introuvable ou déjà supprimé.');
                return $this->redirectToRoute('lists_animaux');
            }

            $em = $doctrine->getManager();
            $em->remove($animal);
            $em->flush();

            return $this->redirectToRoute('lists_animaux');
        }
    }
