<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Form\AnimalType;
use App\Repository\AnimalRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/animal')]
#[IsGranted('ROLE_USER')] // ✅ Connexion obligatoire pour toutes les routes /animal
final class AnimalController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(AnimalRepository $animalRepository): Response
    {
        $user = $this->getUser();

        // Admin et Vétérinaire voient tous les animaux, les autres seulement les leurs
        if ($user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_VET')) {
            $animals = $animalRepository->findAll();
        } else {
            $animals = $animalRepository->findBy(['owner' => $user]);
        }

        return $this->render('animal/index.html.twig', [
            'animals' => $animals
        ]);
    }

    #[Route('/add', name: 'addForm_animal')]
    public function addWithForm(Request $request, ManagerRegistry $doctrine): Response
    {
        $animal = new Animal();
        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ✅ Assigner automatiquement l'utilisateur connecté comme propriétaire
            $animal->setOwner($this->getUser());

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
    public function list(Request $request, AnimalRepository $repository, PaginatorInterface $paginator): Response
    {
        $sort = $request->query->get('sort', 'date_enregistrement');
        $order = $request->query->get('order', 'DESC');

        // Créer le QueryBuilder de base
        $qb = $repository->findAllSortedQueryBuilder($sort, $order);

        // ✅ Filtrer par propriétaire si l'utilisateur n'est pas admin/vétérinaire
        $user = $this->getUser();
        if (!$user->hasRole('ROLE_ADMIN') && !$user->hasRole('ROLE_VET')) {
            $qb->where('a.owner = :user')
                ->setParameter('user', $user);
        }

        // Ajouter le tri
        $allowedSorts = [
            'nom' => 'a.nom',
            'type_animal' => 'a.type_animal',
            'sexe' => 'a.sexe',
            'poids' => 'a.poids',
            'couleur' => 'a.couleur',
            'date_naissance' => 'a.date_naissance',
            'date_enregistrement' => 'a.date_enregistrement',
        ];

        $sortField = $allowedSorts[$sort] ?? 'a.date_enregistrement';
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $qb->orderBy($sortField, $order);

        // Paginer
        $animaux = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            10,
            [
                'wrap-queries' => true,
                'sortFieldParameterName' => null,
                'sortDirectionParameterName' => null,
            ]
        );

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
    ): Response
    {
        $animal = $repository->find($id);

        if (!$animal) {
            throw $this->createNotFoundException('Animal introuvable');
        }

        // ✅ Vérifier que l'utilisateur peut modifier cet animal
        $user = $this->getUser();
        $canEdit = $user->hasRole('ROLE_ADMIN') ||
            $user->hasRole('ROLE_VET') ||
            $animal->getOwner() === $user;

        if (!$canEdit) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier cet animal.');
            return $this->redirectToRoute('lists_animaux');
        }

        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $em->flush();

            $this->addFlash('success', 'Animal modifié avec succès !');
            return $this->redirectToRoute('lists_animaux');
        }

        return $this->render('animal/update.html.twig', [
            'formAnimal' => $form->createView(),
            'animal' => $animal,
        ]);
    }

    #[Route('/remove/{id_animal}', name: 'remove_animal')]
    public function deleteAnimal(
        ManagerRegistry $doctrine,
        int $id_animal,
        AnimalRepository $repository
    ): Response
    {
        $animal = $repository->find($id_animal);

        if (!$animal) {
            $this->addFlash('error', 'Animal introuvable ou déjà supprimé.');
            return $this->redirectToRoute('lists_animaux');
        }

        // ✅ Vérifier que l'utilisateur peut supprimer cet animal
        $user = $this->getUser();
        $canDelete = $user->hasRole('ROLE_ADMIN') ||
            $user->hasRole('ROLE_VET') ||
            $animal->getOwner() === $user;

        if (!$canDelete) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer cet animal.');
            return $this->redirectToRoute('lists_animaux');
        }

        $em = $doctrine->getManager();
        $em->remove($animal);
        $em->flush();

        $this->addFlash('success', 'Animal supprimé avec succès !');
        return $this->redirectToRoute('lists_animaux');
    }

    /**
     * ✅ NOUVELLE ROUTE : Voir les détails d'un animal
     */
    #[Route('/{id}/show', name: 'animal_show')]
    public function show(int $id, AnimalRepository $repository): Response
    {
        $animal = $repository->find($id);

        if (!$animal) {
            throw $this->createNotFoundException('Animal introuvable');
        }

        // ✅ Vérifier que l'utilisateur peut voir cet animal
        $user = $this->getUser();
        $canView = $user->hasRole('ROLE_ADMIN') ||
            $user->hasRole('ROLE_VET') ||
            $animal->getOwner() === $user;

        if (!$canView) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à voir cet animal.');
            return $this->redirectToRoute('lists_animaux');
        }

        return $this->render('animal/show.html.twig', [
            'animal' => $animal,
        ]);
    }

    /**
     * ✅ NOUVELLE ROUTE : Mes animaux (uniquement pour l'utilisateur connecté)
     */
    #[Route('/mes-animaux', name: 'animal_mes_animaux')]
    public function mesAnimaux(
        Request $request,
        AnimalRepository $repository,
        PaginatorInterface $paginator
    ): Response
    {
        $user = $this->getUser();

        // --- Tous les animaux de l'utilisateur pour les compteurs ---
        $allAnimals = $repository->findBy(['owner' => $user]);

        $chiens = count(array_filter($allAnimals, fn($a) => $a->getTypeAnimal() === 'Chien'));
        $chats = count(array_filter($allAnimals, fn($a) => $a->getTypeAnimal() === 'Chat'));
        $autres = count(array_filter($allAnimals, fn($a) => !in_array($a->getTypeAnimal(), ['Chien','Chat'])));

        // --- QueryBuilder pour la pagination ---
        $qb = $repository->createQueryBuilder('a')
            ->where('a.owner = :user')
            ->setParameter('user', $user)
            ->orderBy('a.date_enregistrement', 'DESC')
            ->distinct(); // évite la duplication avec les jointures

        $animaux = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            6 // animaux par page
        );

        return $this->render('animal/mes_animaux.html.twig', [
            'animaux' => $animaux,
            'chiens' => $chiens,
            'chats' => $chats,
            'autres' => $autres,
        ]);
    }


}