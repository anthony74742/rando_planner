<?php

namespace App\Controller;

use App\Entity\HikeSession;
use App\Form\HikeSessionType;
use App\Repository\HikeSessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/hike/session')]
final class HikeSessionController extends AbstractController
{
    #[Route(name: 'app_hike_session_index', methods: ['GET'])]
    public function index(HikeSessionRepository $hikeSessionRepository): Response
    {
        return $this->render('hike_session/index.html.twig', [
            'hike_sessions' => $hikeSessionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_hike_session_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $hikeSession = new HikeSession();
        $form = $this->createForm(HikeSessionType::class, $hikeSession);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($hikeSession);
            $entityManager->flush();

            return $this->redirectToRoute('app_hike_session_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hike_session/new.html.twig', [
            'hike_session' => $hikeSession,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_hike_session_show', methods: ['GET'])]
    public function show(HikeSession $hikeSession): Response
    {
        return $this->render('hike_session/show.html.twig', [
            'hike_session' => $hikeSession,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_hike_session_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, HikeSession $hikeSession, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HikeSessionType::class, $hikeSession);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_hike_session_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hike_session/edit.html.twig', [
            'hike_session' => $hikeSession,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_hike_session_delete', methods: ['POST'])]
    public function delete(Request $request, HikeSession $hikeSession, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$hikeSession->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($hikeSession);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_hike_session_index', [], Response::HTTP_SEE_OTHER);
    }
}
