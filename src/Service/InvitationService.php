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
    ) {
    }

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

        // Génère les deux liens (acceptation et refus)
        $urlAccept = $this->urlGenerator->generate('app_invitation_accept', [
            'token' => $token,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $urlDecline = $this->urlGenerator->generate('app_invitation_decline', [
            'token' => $token,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $htmlContent = $this->generateHtmlEmail(
            $sender->getEmail(),
            $session->getDate(),
            $session->getHike()->getTitle(),
            $session->getHike()->getLocation(),
            $session->getHike()->getDistance(),
            $session->getHike()->getDifficulty(),
            $urlAccept,
            $urlDecline,
            $expiresAt
        );

        $emailMessage = (new Email())
            ->from($sender->getEmail())
            ->to($email)
            ->subject('Invitation à une randonnée - ' . $session->getHike()->getTitle())
            ->html($htmlContent);

        $this->mailer->send($emailMessage);
    }

    private function generateHtmlEmail(
        string $senderEmail,
        \DateTimeImmutable $sessionDate,
        string $hikeName,
        string $location,
        float $distance,
        ?string $difficulty,
        string $urlAccept,
        string $urlDecline,
        \DateTimeImmutable $expiresAt
    ): string {
        $formattedSessionDate = $sessionDate->format('d/m/Y à H:i');
        $formattedExpiresAt = $expiresAt->format('d/m/Y');

        $difficultyBlock = '';
        if ($difficulty) {
            $difficultyBlock = <<<HTML
            <li>
                <span class="icon">⛰️</span>
                <strong>{$difficulty}</strong>
            </li>
        HTML;
        }

        $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation à une randonnée</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f9fafb;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-wrapper {
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .header p {
            margin: 8px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 40px;
        }
        .greeting {
            font-size: 16px;
            margin: 0 0 24px 0;
            color: #1f2937;
        }
        .greeting strong {
            color: #059669;
        }
        .invitation-card {
            background-color: #f0fdf4;
            border-left: 4px solid #059669;
            padding: 20px;
            margin: 24px 0;
            border-radius: 8px;
        }
        .invitation-card h2 {
            margin: 0 0 16px 0;
            font-size: 20px;
            color: #1f2937;
        }
        .hike-details {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .hike-details li {
            padding: 8px 0;
            display: flex;
            align-items: center;
            color: #4b5563;
            font-size: 14px;
        }
        .hike-details li strong {
            color: #1f2937;
            font-weight: 600;
            margin-left: 8px;
        }
        .icon {
            width: 20px;
            display: inline-block;
            text-align: center;
        }
        .cta-section {
            margin: 32px 0;
            padding: 24px 0;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }
        .cta-text {
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 16px;
            text-align: center;
        }
        .button-container {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .button {
            display: inline-block;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            font-size: 15px;
            border: 2px solid transparent;
        }
        .button-accept {
            background-color: #059669;
            color: white;
        }
        .button-accept:hover {
            background-color: #047857;
        }
        .button-decline {
            background-color: #ffffff;
            color: #ef4444;
            border: 2px solid #ef4444;
        }
        .button-decline:hover {
            background-color: #fef2f2;
        }
        .expiry-warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 16px;
            margin: 24px 0;
            border-radius: 6px;
            font-size: 13px;
            color: #92400e;
        }
        .footer {
            padding: 24px 40px;
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 13px;
            color: #6b7280;
        }
        .footer p {
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="email-wrapper">
            <div class="header">
                <h1>Invitation à une randonnée</h1>
                <p>Rejoins-nous pour une aventure inoubliable !</p>
            </div>

            <div class="content">
                <p class="greeting">Salut,</p>
                <p class="greeting"><strong>{$senderEmail}</strong> t'a invité à une randonnée et aimerait que tu les rejoignes !</p>

                <div class="invitation-card">
                    <h2>📍 {$hikeName}</h2>
                    <ul class="hike-details">
                        <li>
                            <span class="icon">📅</span>
                            <strong>{$formattedSessionDate}</strong>
                        </li>
                        <li>
                            <span class="icon">🗺️</span>
                            <strong>{$location}</strong>
                        </li>
                        <li>
                            <span class="icon">📏</span>
                            <strong>{$distance} km</strong>
                        </li>
                        {$difficultyBlock}
                    </ul>
                </div>

                <div class="cta-section">
                    <p class="cta-text">Que décides-tu ?</p>
                    <div class="button-container">
                        <a href="{$urlAccept}" class="button button-accept">✓ Accepter</a>
                        <a href="{$urlDecline}" class="button button-decline">✕ Refuser</a>
                    </div>
                </div>

                <div class="expiry-warning">
                    <strong>⏰ Attention :</strong> Ce lien expirera le <strong>{$formattedExpiresAt}</strong>. Assure-toi de répondre avant cette date.
                </div>
            </div>

            <div class="footer">
                <p>© 2025 Randonnées. Tous les droits réservés.</p>
                <p>Si tu as des questions, contacte directement l'organisateur.</p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

        return $html;
    }
}
