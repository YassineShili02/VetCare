<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\ChangePasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        // Si l'utilisateur n'est pas connecté, rediriger vers la connexion
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        // Seuls les ADMIN peuvent voir tous les utilisateurs
        if (!$this->isGranted('ROLE_ADMIN')) {
            // Les utilisateurs normaux sont redirigés vers leur propre profil
            $this->addFlash('info', 'Vous ne pouvez voir que votre propre profil.');
            return $this->redirectToRoute('app_user_show', ['id' => $this->getUser()->getId()]);
        }

        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Si l'utilisateur est déjà connecté et n'est pas ADMIN, rediriger vers son profil
        if ($this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('info', 'Vous êtes déjà inscrit.');
            return $this->redirectToRoute('app_user_show', ['id' => $this->getUser()->getId()]);
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_creation' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            // Par défaut, les nouveaux utilisateurs ont le rôle USER (sauf si ADMIN définit d'autres rôles)
            if (empty($user->getRoles()) || ($this->getUser() && !$this->isGranted('ROLE_ADMIN'))) {
                $user->setRoles(['ROLE_USER']);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès.');

            // Rediriger vers la connexion si l'utilisateur n'est pas connecté (inscription publique)
            if (!$this->getUser()) {
                $this->addFlash('info', 'Vous pouvez maintenant vous connecter avec vos identifiants.');
                return $this->redirectToRoute('app_login');
            }

            // Si un ADMIN crée un utilisateur, retour à la liste
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        // Si l'utilisateur n'est pas connecté, rediriger vers la connexion
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        // Un utilisateur ne peut voir que son propre profil, sauf si ADMIN
        if ($this->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Accès refusé. Vous ne pouvez voir que votre propre profil.');
            return $this->redirectToRoute('app_user_show', ['id' => $this->getUser()->getId()]);
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Si l'utilisateur n'est pas connecté, rediriger vers la connexion
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        // Un utilisateur ne peut modifier que son propre profil, sauf si ADMIN
        if ($this->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Accès refusé. Vous ne pouvez modifier que votre propre profil.');
            return $this->redirectToRoute('app_user_show', ['id' => $this->getUser()->getId()]);
        }

        $form = $this->createForm(UserType::class, $user, ['is_creation' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès.');

            return $this->redirectToRoute('app_user_show', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/change-password', name: 'app_user_change_password', methods: ['GET', 'POST'])]
    public function changePassword(
        Request $request, 
        User $user, 
        EntityManagerInterface $entityManager, 
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Si l'utilisateur n'est pas connecté, rediriger vers la connexion
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        // Un utilisateur ne peut changer que son propre mot de passe, sauf si ADMIN
        if ($this->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Accès refusé. Vous ne pouvez changer que votre propre mot de passe.');
            return $this->redirectToRoute('app_user_show', ['id' => $this->getUser()->getId()]);
        }

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Pour les ADMIN qui changent le mot de passe d'autres utilisateurs, pas besoin de vérifier l'ancien mot de passe
            if ($this->getUser() === $user) {
                // Vérifier le mot de passe actuel pour l'utilisateur lui-même
                if (!$passwordHasher->isPasswordValid($user, $data['currentPassword'])) {
                    $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
                    return $this->redirectToRoute('app_user_change_password', ['id' => $user->getId()]);
                }
            }

            // Hasher et sauvegarder le nouveau mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $data['newPassword']);
            $user->setPassword($hashedPassword);
            $user->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            if ($this->getUser() === $user) {
                $this->addFlash('success', 'Votre mot de passe a été modifié avec succès.');
            } else {
                $this->addFlash('success', 'Le mot de passe de l\'utilisateur a été modifié avec succès.');
            }

            return $this->redirectToRoute('app_user_show', ['id' => $user->getId()]);
        }

        return $this->render('user/change_password.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Si l'utilisateur n'est pas connecté, rediriger vers la connexion
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        // Seuls les ADMIN peuvent supprimer des utilisateurs
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Accès refusé. Vous devez être administrateur pour supprimer un utilisateur.');
            return $this->redirectToRoute('app_user_show', ['id' => $this->getUser()->getId()]);
        }

        // Empêcher un admin de se supprimer lui-même
        if ($this->getUser() === $user) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            return $this->redirectToRoute('app_user_index');
        }

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}