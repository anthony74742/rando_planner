<?php

namespace App\Service;

use App\Entity\Invitation;
use App\Entity\User;
use App\Entity\HikeSession;
use App\Repository\InvitationRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InvitationService
{
    public function __construct(
        private InvitationRepository $repo,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function invite(User $sender, string $email, HikeSession $session): void
    {
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $expiresAt = (new \DateTimeImmutable())->modify('+7 days');

        $invitation = (new Invitation())
            ->setEmail($email)
            ->setSender($sender)
            ->setSession($session)
            ->setStatus('pending')
            ->setToken($token)
            ->setSentAt(new \DateTimeImmutable())
            ->setExpiresAt($expiresAt);

        $this->repo->save($invitation, true);

        $url = $this->urlGenerator->generate('app_invitation_accept', [
            'token' => $token,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $emailMessage = (new Email())
            ->from($sender->getEmail())
            ->to($email)
            ->subject('Invitation à une randonnée')
            ->text(sprintf(
                "%s t’a invité à une randonnée prévue le %s.\n\nLien valable jusqu’au %s :\n%s",
                $sender->getEmail(),
                $session->getDate()->format('d/m/Y H:i'),
                $expiresAt->format('d/m/Y H:i'),
                $url
            ));

        $this->mailer->send($emailMessage);
    }
}
