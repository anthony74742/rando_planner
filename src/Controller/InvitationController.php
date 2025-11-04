<?php

namespace App\Controller;

use App\Repository\InvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\InvitationService;
use App\Repository\HikeSessionRepository;
use Symfony\Component\HttpFoundation\Request;

#[Route('/invitation')]
class InvitationController extends AbstractController
{
    #[Route('/send/{id}', name: 'app_invitation_send', methods: ['POST'])]
    public function send(
        int $id,
        Request $request,
        HikeSessionRepository $sessionRepo,
        InvitationService $invitationService
    ): Response {
        $session = $sessionRepo->find($id);

        if (!$session) {
            throw $this->createNotFoundException('Session de randonnée introuvable.');
        }

        $this->denyAccessUnlessGranted('ROLE_USER');

        $email = $request->request->get('email');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('danger', 'Adresse e-mail invalide.');
            return $this->redirectToRoute('app_hike_session_show', ['id' => $session->getId()]);
        }

        $invitationService->invite($this->getUser(), $email, $session);

        $this->addFlash('success', "Invitation envoyée à $email !");
        return $this->redirectToRoute('app_hike_session_show', ['id' => $session->getId()]);
    }

    #[Route('/{token}', name: 'app_invitation_accept')]
    public function accept(
        string $token,
        InvitationRepository $repo,
        EntityManagerInterface $em
    ): Response {
        $invitation = $repo->findOneBy(['token' => $token]);

        if (!$invitation) {
            throw $this->createNotFoundException('Lien d’invitation invalide.');
        }

        if ($invitation->isExpired()) {
            $this->addFlash('danger', 'Ce lien d’invitation a expiré.');
            return $this->redirectToRoute('app_home');
        }

        if ($invitation->getStatus() !== 'pending') {
            $this->addFlash('info', 'Cette invitation a déjà été utilisée.');
            return $this->redirectToRoute('app_home');
        }

        // Si l’utilisateur est connecté, il accepte l’invitation directement
        if ($user = $this->getUser()) {
            $invitation->setReceiver($user);
            $invitation->setStatus('accepted');
            $em->flush();

            $this->addFlash('success', 'Invitation acceptée !');
            return $this->redirectToRoute('app_hike_session_show', [
                'id' => $invitation->getSession()->getId(),
            ]);
        }

        // Sinon, on affiche une page publique d’invitation
        return $this->render('invitation/public.html.twig', [
            'invitation' => $invitation,
        ]);
    }
}
