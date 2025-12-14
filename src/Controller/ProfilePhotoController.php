<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ProfilePhotoController extends AbstractController
{
    #[Route('/profile/update-photo', name: 'api_profile_update_photo', methods: ['POST'])]
    public function updateProfilePhoto(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Vérifier que l'utilisateur est connecté
        if (!$this->getUser()) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $userId = $request->request->get('userId');
        
        // Debug
        error_log("=== API PROFILE PHOTO UPDATE ===");
        error_log("User ID reçu: " . $userId);

        $user = $entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        // Vérifier les permissions
        if ($user->getId() !== $this->getUser()->getId() && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'Permission refusée'], 403);
        }

        try {
            $uploadedFile = $request->files->get('profileImage');
            $avatarPath = $request->request->get('avatarPath');

            error_log("Fichier reçu: " . ($uploadedFile ? 'OUI' : 'NON'));
            error_log("Avatar path: " . ($avatarPath ?: 'NULL'));

            if ($uploadedFile) {
                return $this->handleFileUpload($uploadedFile, $user, $entityManager);
            } elseif ($avatarPath) {
                return $this->handleAvatar($avatarPath, $user, $entityManager);
            } else {
                return $this->json(['error' => 'Aucune donnée valide reçue'], 400);
            }

        } catch (\Exception $e) {
            error_log("ERREUR: " . $e->getMessage());
            return $this->json(['error' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    private function handleFileUpload($uploadedFile, User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $fileName = uniqid() . '.' . $uploadedFile->guessExtension();
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profile/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadedFile->move($uploadDir, $fileName);
        $this->updateUserProfile($user, $fileName, $entityManager);

        return $this->json([
            'success' => true,
            'profileImage' => $fileName,
            'message' => 'Photo uploadée avec succès'
        ]);
    }

    private function handleAvatar($avatarPath, User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $fileName = uniqid() . '.png';
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profile/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $avatarContent = file_get_contents($avatarPath);
        file_put_contents($uploadDir . $fileName, $avatarContent);
        $this->updateUserProfile($user, $fileName, $entityManager);

        return $this->json([
            'success' => true,
            'profileImage' => $fileName,
            'message' => 'Avatar sélectionné avec succès'
        ]);
    }

    private function updateUserProfile(User $user, string $fileName, EntityManagerInterface $entityManager): void
    {
        if ($user->getProfileImage()) {
            $oldFile = $this->getParameter('kernel.project_dir') . '/public/uploads/profile/' . $user->getProfileImage();
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        $user->setProfileImage($fileName);
        $user->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->flush();
    }
}