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
<<<<<<< HEAD
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
=======
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee

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

<<<<<<< HEAD
        // Récupérer les utilisateurs avec filtres par défaut
        $users = $userRepository->searchAndFilter([], ['field' => 'u.lastName', 'direction' => 'ASC']);
        
        // Statistiques
        $stats = $userRepository->countByRole();

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'filters' => [],
            'sort' => ['field' => 'u.lastName', 'direction' => 'ASC'],
            'stats' => $stats,
            'total_users' => count($users),
        ]);
    }

    #[Route('/filter', name: 'app_user_filter', methods: ['GET', 'POST'])]
    public function filter(Request $request, UserRepository $userRepository): Response
    {
        if (!$this->getUser() || !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Accès refusé. Seuls les administrateurs peuvent filtrer les utilisateurs.');
            return $this->redirectToRoute('app_user_index');
        }

        $filters = [];
        $sort = [];

        // Récupération des filtres depuis la requête
        if ($request->isMethod('POST')) {
            $formData = $request->request->all();
            
            // Filtres
            if (!empty($formData['search'])) {
                $filters['search'] = $formData['search'];
            }
            
            if (!empty($formData['role'])) {
                $filters['role'] = $formData['role'];
            }
            
            if (!empty($formData['created_from'])) {
                $filters['created_from'] = new \DateTime($formData['created_from']);
            }
            
            if (!empty($formData['created_to'])) {
                $filters['created_to'] = new \DateTime($formData['created_to']);
            }
            
            // Tri
            if (!empty($formData['sort_field'])) {
                $sort['field'] = $formData['sort_field'];
            }
            
            if (!empty($formData['sort_direction'])) {
                $sort['direction'] = $formData['sort_direction'];
            }
        } else {
            // Filtres par défaut depuis les paramètres GET
            $filters['search'] = $request->query->get('search', '');
            $filters['role'] = $request->query->get('role', '');
            
            $sort['field'] = $request->query->get('sort_field', 'u.lastName');
            $sort['direction'] = $request->query->get('sort_direction', 'ASC');
        }

        $users = $userRepository->searchAndFilter($filters, $sort);

        // Statistiques
        $stats = $userRepository->countByRole();

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'filters' => $filters,
            'sort' => $sort,
            'stats' => $stats,
            'total_users' => count($users),
=======
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
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
<<<<<<< HEAD
        $isAdminCreating = $this->isGranted('ROLE_ADMIN') && $this->getUser();
        
        // MODIFICATION : Toujours afficher les rôles lors de la création
        $form = $this->createForm(UserType::class, $user, [
            'is_creation' => true,
            'show_roles' => true,
            'show_profile_photo' => $isAdminCreating,
            'allow_admin_role' => true // MODIFICATION : TRUE pour permettre à tous de créer un admin
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Gestion du mot de passe
                if ($form->has('password')) {
                    $plainPassword = $form->get('password')->getData();
                    if ($plainPassword) {
                        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                        $user->setPassword($hashedPassword);
                    }
                }

                // Gestion des rôles - MODIFICATION : Autoriser tout le monde à créer un compte admin
                if ($form->has('role')) {
                    $selectedRole = $form->get('role')->getData();
                    
                    // Validation du rôle
                    $availableRoles = ['ROLE_USER', 'ROLE_VET', 'ROLE_ADMIN'];
                    
                    // SUPPRIMÉ : Pas de restriction pour les non-admins
                    // Tout le monde peut créer un compte admin maintenant
                    
                    if ($selectedRole && in_array($selectedRole, $availableRoles)) {
                        $user->setRoles([$selectedRole]);
                    } else {
                        $user->setRoles(['ROLE_USER']); // Rôle par défaut
                    }
                } else {
                    // Si pas de champ role, mettre rôle USER par défaut
                    $user->setRoles(['ROLE_USER']);
                }

                $user->setCreatedAt(new \DateTimeImmutable());
                $user->setUpdatedAt(new \DateTimeImmutable());

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Utilisateur créé avec succès.');
                
                // Message spécifique selon le rôle
                $roleMessage = [
                    'ROLE_USER' => 'Bienvenue en tant que client! Vous pouvez maintenant gérer vos animaux.',
                    'ROLE_VET' => 'Bienvenue en tant que vétérinaire! Votre compte doit être validé par un administrateur.',
                    'ROLE_ADMIN' => 'Bienvenue en tant qu\'administrateur!'
                ];
                
                $mainRole = $user->getMainRole();
                if (isset($roleMessage[$mainRole])) {
                    $this->addFlash('info', $roleMessage[$mainRole]);
                }

                if (!$this->getUser()) {
                    $this->addFlash('info', 'Vous pouvez maintenant vous connecter avec vos identifiants.');
                    return $this->redirectToRoute('app_login');
                }

                return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création de l\'utilisateur: ' . $e->getMessage());
            }
=======
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
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
<<<<<<< HEAD
            'is_admin_creating' => $isAdminCreating
=======
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
<<<<<<< HEAD
=======
        // Si l'utilisateur n'est pas connecté, rediriger vers la connexion
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

<<<<<<< HEAD
        // Seuls les ADMIN peuvent voir tous les profils
=======
        // Un utilisateur ne peut voir que son propre profil, sauf si ADMIN
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
        if ($this->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Accès refusé. Vous ne pouvez voir que votre propre profil.');
            return $this->redirectToRoute('app_user_show', ['id' => $this->getUser()->getId()]);
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
<<<<<<< HEAD
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
=======
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Si l'utilisateur n'est pas connecté, rediriger vers la connexion
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

<<<<<<< HEAD
        // Seuls les ADMIN peuvent modifier tous les profils
=======
        // Un utilisateur ne peut modifier que son propre profil, sauf si ADMIN
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
        if ($this->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Accès refusé. Vous ne pouvez modifier que votre propre profil.');
            return $this->redirectToRoute('app_user_show', ['id' => $this->getUser()->getId()]);
        }

<<<<<<< HEAD
        $isAdminEditing = $this->isGranted('ROLE_ADMIN') && $this->getUser();
        
        $form = $this->createForm(UserType::class, $user, [
            'is_creation' => false,
            'show_roles' => $isAdminEditing,
            'show_profile_photo' => $isAdminEditing,
            'allow_admin_role' => $isAdminEditing
        ]);

        // Pré-remplir le champ role avec le rôle principal de l'utilisateur
        if ($form->has('role')) {
            $form->get('role')->setData($user->getMainRole());
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Gestion du mot de passe (si l'admin change le mot de passe)
                if ($form->has('password') && $form->get('password')->getData()) {
                    $plainPassword = $form->get('password')->getData();
                    $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                    $user->setPassword($hashedPassword);
                }

                // Gestion des rôles - CORRECTION : récupérer depuis le champ 'role' (non mappé)
                if ($form->has('role')) {
                    $selectedRole = $form->get('role')->getData();
                    if ($selectedRole) {
                        $user->setRoles([$selectedRole]); // Transforme string en array
                    }
                }

                $user->setUpdatedAt(new \DateTimeImmutable());

                $entityManager->flush();

                $this->addFlash('success', 'Utilisateur mis à jour avec succès.');
                return $this->redirectToRoute('app_user_show', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour de l\'utilisateur: ' . $e->getMessage());
            }
=======
        $form = $this->createForm(UserType::class, $user, ['is_creation' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès.');

            return $this->redirectToRoute('app_user_show', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

<<<<<<< HEAD
    #[Route('/{id}/change-password-ajax', name: 'app_user_change_password_ajax', methods: ['POST'])]
    public function changePasswordAjax(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): JsonResponse {
        // Vérifier les permissions
        if (!$this->getUser() || ($this->getUser() !== $user && !$this->isGranted('ROLE_ADMIN'))) {
            return $this->json([
                'success' => false,
                'message' => 'Accès refusé. Vous n\'avez pas le droit de modifier ce mot de passe.'
            ], 403);
        }

        // Vérifier que c'est une requête AJAX
        if (!$request->isXmlHttpRequest()) {
            return $this->json([
                'success' => false,
                'message' => 'Cette route accepte uniquement les requêtes AJAX'
            ], 400);
        }

        // Récupérer les données JSON
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->json([
                'success' => false,
                'message' => 'Données invalides'
            ], 400);
        }

        // Valider les champs obligatoires
        $requiredFields = ['current_password', 'new_password', 'confirm_password', '_token'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Le champ "' . str_replace('_', ' ', $field) . '" est obligatoire'
                ], 400);
            }
        }

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('change_password_' . $user->getId(), $data['_token'])) {
            return $this->json([
                'success' => false,
                'message' => 'Token CSRF invalide'
            ], 400);
        }

        // Pour les ADMIN qui changent le mot de passe d'autres utilisateurs
        $isAdminChangingOtherPassword = $this->isGranted('ROLE_ADMIN') && $this->getUser() !== $user;
        
        if ($isAdminChangingOtherPassword) {
            // Les admins doivent fournir leur propre mot de passe pour changer celui d'un autre
            if (empty($data['admin_current_password'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Pour des raisons de sécurité, veuillez entrer votre mot de passe administrateur'
                ], 400);
            }
            
            if (!$passwordHasher->isPasswordValid($this->getUser(), $data['admin_current_password'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Votre mot de passe administrateur est incorrect'
                ], 400);
            }
        } else {
            // Pour l'utilisateur qui change son propre mot de passe
            if (!$passwordHasher->isPasswordValid($user, $data['current_password'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Le mot de passe actuel est incorrect'
                ], 400);
            }
        }

        // Vérifier la correspondance des nouveaux mots de passe
        if ($data['new_password'] !== $data['confirm_password']) {
            return $this->json([
                'success' => false,
                'message' => 'Les nouveaux mots de passe ne correspondent pas'
            ], 400);
        }

        // Vérifier la force du mot de passe
        if (strlen($data['new_password']) < 6) {
            return $this->json([
                'success' => false,
                'message' => 'Le nouveau mot de passe doit contenir au moins 6 caractères'
            ], 400);
        }

        // Vérifier que le nouveau mot de passe est différent de l'ancien
        if ($passwordHasher->isPasswordValid($user, $data['new_password'])) {
            return $this->json([
                'success' => false,
                'message' => 'Le nouveau mot de passe doit être différent de l\'ancien'
            ], 400);
        }

        try {
            // Hacher et mettre à jour le mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $data['new_password']);
            $user->setPassword($hashedPassword);
            $user->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Mot de passe changé avec succès',
                'redirect_url' => $this->generateUrl('app_user_show', ['id' => $user->getId()])
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la mise à jour du mot de passe: ' . $e->getMessage()
            ], 500);
        }
    }

=======
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
    #[Route('/{id}/change-password', name: 'app_user_change_password', methods: ['GET', 'POST'])]
    public function changePassword(
        Request $request, 
        User $user, 
        EntityManagerInterface $entityManager, 
        UserPasswordHasherInterface $passwordHasher
    ): Response {
<<<<<<< HEAD
=======
        // Si l'utilisateur n'est pas connecté, rediriger vers la connexion
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

<<<<<<< HEAD
=======
        // Un utilisateur ne peut changer que son propre mot de passe, sauf si ADMIN
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
        if ($this->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Accès refusé. Vous ne pouvez changer que votre propre mot de passe.');
            return $this->redirectToRoute('app_user_show', ['id' => $this->getUser()->getId()]);
        }

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

<<<<<<< HEAD
            try {
                // Pour les ADMIN qui changent le mot de passe d'autres utilisateurs
                if ($this->isGranted('ROLE_ADMIN') && $this->getUser() !== $user) {
                    // Dans la version formulaire traditionnel, l'admin peut directement changer le mot de passe
                    // sans vérification supplémentaire pour simplifier
                } else {
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
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors du changement de mot de passe: ' . $e->getMessage());
            }
=======
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
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
        }

        return $this->render('user/change_password.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

<<<<<<< HEAD
    #[Route('/{id}/upload-photo', name: 'app_user_upload_photo', methods: ['POST'])]
    public function uploadPhoto(
        Request $request, 
        User $user, 
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): JsonResponse {
        if (!$this->getUser() || ($this->getUser() !== $user && !$this->isGranted('ROLE_ADMIN'))) {
            return $this->json([
                'success' => false,
                'message' => 'Accès refusé'
            ], 403);
        }

        $file = $request->files->get('profile_photo');
        
        if (!$file instanceof UploadedFile) {
            return $this->json([
                'success' => false,
                'message' => 'Aucun fichier uploadé'
            ], 400);
        }

        // Valider le fichier
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return $this->json([
                'success' => false,
                'message' => 'Format de fichier non supporté. Utilisez JPG, PNG, GIF ou WebP.'
            ], 400);
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return $this->json([
                'success' => false,
                'message' => 'Le fichier est trop volumineux (max 5MB)'
            ], 400);
        }

        try {
            // Supprimer l'ancienne photo si elle existe
            $this->deleteProfilePhoto($user);
            
            // Déterminer le dossier en fonction du rôle
            $roleFolder = 'users';
            if (in_array('ROLE_VET', $user->getRoles())) {
                $roleFolder = 'vets';
            } elseif (in_array('ROLE_ADMIN', $user->getRoles())) {
                $roleFolder = 'admins';
            }

            // Créer le dossier s'il n'existe pas
            $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads/profiles/'.$roleFolder.'/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Générer un nom de fichier sécurisé
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = preg_replace('/[^a-zA-Z0-9]/', '_', $originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

            // Sauvegarder le fichier
            $file->move($uploadDir, $newFilename);
            
            // Mettre à jour l'utilisateur
            $photoUrl = '/uploads/profiles/'.$roleFolder.'/'.$newFilename;
            $user->setProfilePhoto($photoUrl);
            $user->setUpdatedAt(new \DateTimeImmutable());
            
            $entityManager->flush();
            
            return $this->json([
                'success' => true,
                'photo_url' => $photoUrl,
                'message' => 'Photo de profil mise à jour avec succès'
            ]);
            
        } catch (FileException $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload: '.$e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Une erreur est survenue: '.$e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}/save-avatar', name: 'app_user_save_avatar', methods: ['POST'])]
    public function saveAvatar(
        Request $request, 
        User $user, 
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): JsonResponse {
        if (!$this->getUser() || ($this->getUser() !== $user && !$this->isGranted('ROLE_ADMIN'))) {
            return $this->json([
                'success' => false,
                'message' => 'Accès refusé'
            ], 403);
        }

        $data = json_decode($request->getContent(), true);
        $avatarData = $data['avatar_data'] ?? null;
        $avatarImage = $data['avatar_image'] ?? null;

        if (!$avatarImage && !$avatarData) {
            return $this->json([
                'success' => false,
                'message' => 'Données d\'avatar invalides'
            ], 400);
        }

        try {
            // Si c'est une image base64, la sauvegarder
            if ($avatarImage && str_starts_with($avatarImage, 'data:image/')) {
                // Extraire le type et les données
                $matches = [];
                if (!preg_match('/^data:image\/(\w+);base64,/', $avatarImage, $matches)) {
                    throw new \Exception('Format d\'image base64 invalide');
                }
                
                $imageType = $matches[1];
                $avatarImage = preg_replace('/^data:image\/\w+;base64,/', '', $avatarImage);
                $avatarImage = str_replace(' ', '+', $avatarImage);
                $imageData = base64_decode($avatarImage);
                
                if (!$imageData) {
                    throw new \Exception('Impossible de décoder l\'image base64');
                }
                
                // Supprimer l'ancienne photo si elle existe
                $this->deleteProfilePhoto($user);
                
                // Déterminer le dossier
                $roleFolder = 'users';
                if (in_array('ROLE_VET', $user->getRoles())) {
                    $roleFolder = 'vets';
                } elseif (in_array('ROLE_ADMIN', $user->getRoles())) {
                    $roleFolder = 'admins';
                }
                
                // Créer le dossier s'il n'existe pas
                $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads/profiles/'.$roleFolder.'/avatars/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Générer un nom de fichier
                $extension = $imageType === 'jpeg' ? 'jpg' : $imageType;
                $filename = 'avatar-' . $user->getId() . '-' . uniqid() . '.' . $extension;
                $filepath = $uploadDir . $filename;
                
                // Sauvegarder l'image
                if (file_put_contents($filepath, $imageData) === false) {
                    throw new \Exception('Impossible de sauvegarder l\'image');
                }
                
                // Mettre à jour l'utilisateur
                $avatarUrl = '/uploads/profiles/'.$roleFolder.'/avatars/'.$filename;
                $user->setProfilePhoto($avatarUrl);
            } 
            // Sinon, utiliser les données d'avatar pour générer une URL
            elseif ($avatarData) {
                // Générer une URL d'avatar basée sur les données
                $avatarUrl = $this->generateAvatarUrl($avatarData, $user);
                
                // Supprimer l'ancienne photo physique si elle existe
                $this->deleteProfilePhoto($user);
                
                $user->setProfilePhoto($avatarUrl);
            }
            
            $user->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'photo_url' => $user->getProfilePhoto(),
                'message' => 'Avatar mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde de l\'avatar: '.$e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}/update-photo-ajax', name: 'app_user_update_photo_ajax', methods: ['POST'])]
    public function updatePhotoAjax(
        Request $request, 
        User $user, 
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): JsonResponse {
        if (!$this->getUser() || ($this->getUser() !== $user && !$this->isGranted('ROLE_ADMIN'))) {
            return $this->json([
                'success' => false,
                'message' => 'Accès refusé'
            ], 403);
        }

        $data = json_decode($request->getContent(), true);
        $csrfToken = $data['_token'] ?? null;

        if (!$csrfToken || !$this->isCsrfTokenValid('update_photo_' . $user->getId(), $csrfToken)) {
            return $this->json([
                'success' => false,
                'message' => 'Token CSRF invalide'
            ], 400);
        }

        try {
            $newPhotoUrl = null;

            // Cas 1: Avatar sélectionné depuis la galerie OU photo uploadée
            if (isset($data['photo_data'])) {
                $photoData = $data['photo_data'];
                
                // Vérifier si c'est une image base64
                if (str_starts_with($photoData, 'data:image/')) {
                    // Extraire les données
                    $matches = [];
                    if (!preg_match('/^data:image\/(\w+);base64,/', $photoData, $matches)) {
                        throw new \Exception('Format d\'image base64 invalide');
                    }
                    
                    $imageType = $matches[1];
                    $photoData = preg_replace('/^data:image\/\w+;base64,/', '', $photoData);
                    $photoData = str_replace(' ', '+', $photoData);
                    $imageData = base64_decode($photoData);
                    
                    if (!$imageData) {
                        throw new \Exception('Impossible de décoder l\'image');
                    }
                    
                    // Déterminer le dossier par rôle
                    $roleFolder = 'users';
                    if (in_array('ROLE_VET', $user->getRoles())) {
                        $roleFolder = 'vets';
                    } elseif (in_array('ROLE_ADMIN', $user->getRoles())) {
                        $roleFolder = 'admins';
                    }
                    
                    // Créer le dossier
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles/' . $roleFolder . '/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    // Supprimer l'ancienne photo si elle existe
                    $this->deleteProfilePhoto($user);
                    
                    // Générer un nom de fichier
                    $extension = $imageType === 'jpeg' ? 'jpg' : $imageType;
                    
                    // Si c'est un avatar généré, inclure le type et l'index dans le nom
                    if (isset($data['avatar_type']) && isset($data['avatar_index'])) {
                        $filename = 'avatar-' . $data['avatar_type'] . '-' . $data['avatar_index'] . '-' . $user->getId() . '-' . uniqid() . '.' . $extension;
                    } else {
                        $filename = 'profile-' . $user->getId() . '-' . uniqid() . '.' . $extension;
                    }
                    
                    $filepath = $uploadDir . $filename;
                    
                    // Sauvegarder l'image
                    if (file_put_contents($filepath, $imageData) === false) {
                        throw new \Exception('Impossible de sauvegarder l\'image');
                    }
                    
                    $newPhotoUrl = '/uploads/profiles/' . $roleFolder . '/' . $filename;
                }
            }
            
            if (!$newPhotoUrl) {
                return $this->json([
                    'success' => false,
                    'message' => 'Aucune donnée photo valide'
                ], 400);
            }
            
            // Mettre à jour l'utilisateur
            $user->setProfilePhoto($newPhotoUrl);
            $user->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'photo_url' => $newPhotoUrl,
                'message' => 'Photo de profil mise à jour avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}/reset-photo-ajax', name: 'app_user_reset_photo_ajax', methods: ['POST'])]
    public function resetPhotoAjax(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        if (!$this->getUser() || ($this->getUser() !== $user && !$this->isGranted('ROLE_ADMIN'))) {
            return $this->json([
                'success' => false,
                'message' => 'Accès refusé'
            ], 403);
        }

        $data = json_decode($request->getContent(), true);
        $csrfToken = $data['_token'] ?? null;

        if (!$csrfToken || !$this->isCsrfTokenValid('reset_photo_' . $user->getId(), $csrfToken)) {
            return $this->json([
                'success' => false,
                'message' => 'Token CSRF invalide'
            ], 400);
        }

        try {
            // Supprimer l'ancienne photo si elle existe
            $this->deleteProfilePhoto($user);
            
            // Réinitialiser à l'avatar par défaut
            $user->setProfilePhoto(null);
            $user->setUpdatedAt(new \DateTimeImmutable());
            
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Photo de profil réinitialisée',
                'default_photo' => '/front/images/default-avatar.png'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation de la photo: '.$e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
=======
    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Si l'utilisateur n'est pas connecté, rediriger vers la connexion
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

<<<<<<< HEAD
=======
        // Seuls les ADMIN peuvent supprimer des utilisateurs
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Accès refusé. Vous devez être administrateur pour supprimer un utilisateur.');
            return $this->redirectToRoute('app_user_show', ['id' => $this->getUser()->getId()]);
        }

<<<<<<< HEAD
=======
        // Empêcher un admin de se supprimer lui-même
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
        if ($this->getUser() === $user) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            return $this->redirectToRoute('app_user_index');
        }

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
<<<<<<< HEAD
            try {
                // Supprimer la photo de profil si elle existe
                $this->deleteProfilePhoto($user);
                
                $entityManager->remove($user);
                $entityManager->flush();
                
                $this->addFlash('success', 'Utilisateur supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression de l\'utilisateur: ' . $e->getMessage());
            }
=======
            $entityManager->remove($user);
            $entityManager->flush();
            
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
<<<<<<< HEAD

    #[Route('/export/filtered', name: 'app_user_export_filtered', methods: ['GET'])]
    public function exportFiltered(Request $request, UserRepository $userRepository): Response
    {
        if (!$this->getUser() || !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('app_user_index');
        }

        $filters = [];
        $sort = [];

        // Récupération des mêmes filtres que la page
        $filters['search'] = $request->query->get('search', '');
        $filters['role'] = $request->query->get('role', '');
        
        $sort['field'] = $request->query->get('sort_field', 'u.lastName');
        $sort['direction'] = $request->query->get('sort_direction', 'ASC');

        $users = $userRepository->searchAndFilter($filters, $sort);
        
        $csvContent = "ID,Prénom,Nom,Email,Rôles,Téléphone,Adresse,Créé le,Modifié le\n";
        
        foreach ($users as $user) {
            $csvContent .= sprintf(
                '%d,%s,%s,%s,%s,%s,%s,%s,%s',
                $user->getId(),
                $this->escapeCsv($user->getFirstName()),
                $this->escapeCsv($user->getLastName()),
                $this->escapeCsv($user->getEmail()),
                $this->escapeCsv(implode(', ', $user->getRoles())),
                $this->escapeCsv($user->getPhone() ?? ''),
                $this->escapeCsv($user->getAddress() ?? ''),
                $user->getCreatedAt()->format('d/m/Y H:i'),
                $user->getUpdatedAt() ? $user->getUpdatedAt()->format('d/m/Y H:i') : ''
            ) . "\n";
        }

        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="utilisateurs_filtres_' . date('Y-m-d') . '.csv"');

        return $response;
    }

    #[Route('/stats', name: 'app_user_stats', methods: ['GET'])]
    public function stats(UserRepository $userRepository): Response
    {
        if (!$this->getUser() || !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('app_user_index');
        }

        $stats = $userRepository->countByRole();
        $recentUsers = $userRepository->findRecentUsers(5);
        $totalUsers = $stats['total'];

        return $this->render('user/stats.html.twig', [
            'stats' => $stats,
            'recent_users' => $recentUsers,
            'total_users' => $totalUsers,
        ]);
    }

    #[Route('/export/csv', name: 'app_user_export_csv', methods: ['GET'])]
    public function exportCsv(UserRepository $userRepository): Response
    {
        if (!$this->getUser() || !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('app_user_index');
        }

        $users = $userRepository->findAllOrderedByName();
        
        $csvContent = "ID,Prénom,Nom,Email,Rôles,Téléphone,Adresse,Créé le,Modifié le\n";
        
        foreach ($users as $user) {
            $csvContent .= sprintf(
                '%d,%s,%s,%s,%s,%s,%s,%s,%s',
                $user->getId(),
                $this->escapeCsv($user->getFirstName()),
                $this->escapeCsv($user->getLastName()),
                $this->escapeCsv($user->getEmail()),
                $this->escapeCsv(implode(', ', $user->getRoles())),
                $this->escapeCsv($user->getPhone() ?? ''),
                $this->escapeCsv($user->getAddress() ?? ''),
                $user->getCreatedAt()->format('d/m/Y H:i'),
                $user->getUpdatedAt() ? $user->getUpdatedAt()->format('d/m/Y H:i') : ''
            ) . "\n";
        }

        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="utilisateurs_' . date('Y-m-d') . '.csv"');

        return $response;
    }

    #[Route('/search/autocomplete', name: 'app_user_search_autocomplete', methods: ['GET'])]
    public function searchAutocomplete(Request $request, UserRepository $userRepository): JsonResponse
    {
        if (!$this->getUser() || !$this->isGranted('ROLE_ADMIN')) {
            return $this->json([], 403);
        }

        $query = $request->query->get('q', '');
        
        if (strlen($query) < 2) {
            return $this->json([]);
        }

        $users = $userRepository->searchByTerm($query, 10);
        
        $results = [];
        foreach ($users as $user) {
            $results[] = [
                'id' => $user->getId(),
                'text' => $user->getFullName() . ' (' . $user->getEmail() . ')',
                'email' => $user->getEmail(),
                'name' => $user->getFullName(),
                'avatar' => $user->getProfilePhoto() ?: '/front/images/default-avatar.png'
            ];
        }

        return $this->json($results);
    }

    /**
     * Supprime la photo de profil physique si elle existe
     */
    private function deleteProfilePhoto(User $user): void
    {
        if ($user->getProfilePhoto() && str_starts_with($user->getProfilePhoto(), '/uploads/profiles/')) {
            $photoPath = $this->getParameter('kernel.project_dir') . '/public' . $user->getProfilePhoto();
            if (file_exists($photoPath) && is_file($photoPath)) {
                @unlink($photoPath);
            }
        }
    }

    /**
     * Génère une URL d'avatar basée sur les données
     */
    private function generateAvatarUrl(array $avatarData, User $user): string
    {
        // Récupérer les initiales de l'utilisateur
        $firstNameInitial = $user->getFirstName() ? substr($user->getFirstName(), 0, 1) : 'U';
        $lastNameInitial = $user->getLastName() ? substr($user->getLastName(), 0, 1) : 'S';
        $initials = strtoupper($firstNameInitial . $lastNameInitial);
        
        // Couleurs basées sur l'index de l'avatar
        $colors = [
            '#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6', '#1abc9c',
            '#d35400', '#c0392b', '#16a085', '#8e44ad', '#2c3e50', '#27ae60'
        ];
        
        $avatarIndex = $avatarData['index'] ?? 1;
        $colorIndex = ($avatarIndex - 1) % count($colors);
        $color = $colors[$colorIndex];
        
        // Générer une URL Gravatar-like avec les paramètres
        $params = http_build_query([
            'name' => $initials,
            'background' => str_replace('#', '', $color),
            'color' => 'fff',
            'size' => '150',
            'font-size' => '0.5',
            'bold' => 'true',
            'length' => '2'
        ]);
        
        // Utiliser un service d'avatar local ou externe
        return 'https://ui-avatars.com/api/?' . $params;
    }

    /**
     * Échappe les caractères spéciaux pour CSV
     */
    private function escapeCsv(string $value): string
    {
        if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
            $value = str_replace('"', '""', $value);
            $value = '"' . $value . '"';
        }
        return $value;
    }
=======
>>>>>>> 0693e84bb198da9483ca0f754855e4147ce8b3ee
}