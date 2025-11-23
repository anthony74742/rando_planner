<?php

namespace App\Controller;

use App\Entity\Hike;
use App\Form\HikeType;
use App\Repository\HikeRepository;
use App\Repository\HikeSessionRepository;
use App\Service\GpxParser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[IsGranted('ROLE_USER')]
#[Route('/hike')]
final class HikeController extends AbstractController
{

    #[Route('/test-mail', name: 'app_test_mail')]
    public function testMail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('test@rando-planner.local')
            ->to('demo@example.com')
            ->subject('Test Mailpit 🏔️')
            ->text('Ceci est un test de Mailpit via Symfony.');

        $mailer->send($email);

        return new Response('Mail envoyé (vérifie Mailpit sur http://localhost:8025)');
    }

    #[Route(name: 'app_hike_index', methods: ['GET'])]
    public function index(HikeRepository $hikeRepository): Response
    {
        $user = $this->getUser();
        $hikes = $hikeRepository->findByUserOrPublic($user);

        return $this->render('hike/index.html.twig', [
            'hikes' => $hikes,
        ]);
    }

    #[Route('/new', name: 'app_hike_new', methods: ['GET', 'POST'])]
    public function new(Request $request, HikeRepository $hikeRepository, GpxParser $gpxParser): Response
    {
        $hike = new Hike();
        $form = $this->createForm(HikeType::class, $hike);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->updateHikeFromGpx($hike, $gpxParser, $form);

            $this->ensureMetricsAreFilled($hike, $form);
            if ($form->getErrors(true)->count() > 0) {
                return $this->render('hike/new.html.twig', [
                    'hike' => $hike,
                    'form' => $form,
                ]);
            }

            $hike->create($this->getUser());
            $hikeRepository->save($hike, true);
            return $this->redirectToRoute('app_hike_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hike/new.html.twig', [
            'hike' => $hike,
            'form' => $form,
        ]);
    }
    #[Route('/{id}', name: 'app_hike_show', methods: ['GET'])]
    public function show(Hike $hike, HikeSessionRepository $hikeSessionRepository): Response
    {
        $hikeSession = $hikeSessionRepository->findBy(['hike' => $hike]);

        return $this->render('hike/show.html.twig', [
            'hike' => $hike,
            'hikeSession' => $hikeSession
        ]);
    }

    #[Route('/{id}/edit', name: 'app_hike_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Hike $hike, EntityManagerInterface $entityManager, GpxParser $gpxParser): Response
    {
        $form = $this->createForm(HikeType::class, $hike);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->updateHikeFromGpx($hike, $gpxParser, $form);
            $this->ensureMetricsAreFilled($hike, $form);

            if ($form->getErrors(true)->count() > 0) {
                return $this->render('hike/edit.html.twig', [
                    'hike' => $hike,
                    'form' => $form,
                ]);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_hike_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hike/edit.html.twig', [
            'hike' => $hike,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_hike_delete', methods: ['POST'])]
    public function delete(Request $request, Hike $hike, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$hike->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($hike);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_hike_index', [], Response::HTTP_SEE_OTHER);
    }

    private function updateHikeFromGpx(Hike $hike, GpxParser $gpxParser, FormInterface $form): void
    {
        $file = $hike->getGpxFile();

        if (!$file instanceof File) {
            return;
        }

        $result = $gpxParser->parse($file->getPathname());

        if (null === $result) {
            $form->get('gpxFile')->addError(new FormError('Le fichier GPX fourni est vide ou invalide.'));
            return;
        }

        if (!empty($result['points'])) {
            $hike->setGpxTrack($result['points']);
            $firstPoint = $result['points'][0];
            $hike->setLatitude($firstPoint['lat']);
            $hike->setLongitude($firstPoint['lng']);
        }

        if (array_key_exists('distance_km', $result) && null !== $result['distance_km']) {
            $hike->setDistance(round($result['distance_km'], 2));
        }

        if (array_key_exists('duration_hours', $result) && null !== $result['duration_hours']) {
            $hike->setDuration(number_format($result['duration_hours'], 2, '.', ''));
        }
    }

    private function ensureMetricsAreFilled(Hike $hike, FormInterface $form): void
    {
        if (null === $hike->getDistance()) {
            $form->get('distance')->addError(new FormError('Veuillez renseigner la distance ou importer un fichier GPX.'));
        }

        if (null === $hike->getDuration()) {
            $form->get('duration')->addError(new FormError('Veuillez renseigner la durée estimée ou importer un fichier GPX.'));
        }
    }
}
