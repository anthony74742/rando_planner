<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/profile/update-email', name: 'app_profile_update_email', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updateEmail(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('update_email', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_profile');
        }

        $user = $this->getUser();
        $newEmail = $request->request->get('email');

        $user->setEmail($newEmail);
        $em->flush();

        $this->addFlash('success', 'Votre email a été mis à jour avec succès.');
        return $this->redirectToRoute('app_profile');
    }

    #[Route('/profile/update-avatar', name: 'app_profile_update_avatar', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updateAvatar(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('update_avatar', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_profile');
        }

        $file = $request->files->get('avatar');

        if (!$file) {
            $this->addFlash('error', 'Veuillez sélectionner une image.');
            return $this->redirectToRoute('app_profile');
        }

        // Vérifier que c'est une image
        $validMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $validMimes)) {
            $this->addFlash('error', 'Seules les images (JPG, PNG, GIF, WebP) sont acceptées.');
            return $this->redirectToRoute('app_profile');
        }

        // Vérifier la taille (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            $this->addFlash('error', 'L\'image ne doit pas dépasser 5MB.');
            return $this->redirectToRoute('app_profile');
        }

        $user = $this->getUser();
        $user->setAvatarFile($file);
        $em->flush();

        $this->addFlash('success', 'Votre photo de profil a été mise à jour avec succès.');
        return $this->redirectToRoute('app_profile');
    }


    #[Route('/profile/update-password', name: 'app_profile_update_password', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updatePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response {
        if (!$this->isCsrfTokenValid('update_password', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_profile');
        }

        $user = $this->getUser();
        $currentPassword = $request->request->get('current_password');
        $newPassword = $request->request->get('new_password');
        $confirmPassword = $request->request->get('confirm_password');

        if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
            $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
            return $this->redirectToRoute('app_profile');
        }

        if ($newPassword !== $confirmPassword) {
            $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
            return $this->redirectToRoute('app_profile');
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);
        $em->flush();

        $this->addFlash('success', 'Votre mot de passe a été changé avec succès.');
        return $this->redirectToRoute('app_profile');
    }

    #[Route('/profile/delete', name: 'app_profile_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function deleteAccount(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete_account', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_profile');
        }

        $user = $this->getUser();
        $em->remove($user);
        $em->flush();

        $this->container->get('security.token_storage')->setToken(null);

        return $this->redirectToRoute('app_home');
    }
}
