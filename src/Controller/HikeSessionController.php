<?php

namespace App\Controller;

use App\Entity\Hike;
use App\Entity\HikeSession;
use App\Entity\User;
use App\Form\HikeSessionType;
use App\Repository\HikeRepository;
use App\Repository\HikeSessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/session')]
final class HikeSessionController extends AbstractController
{
    #[Route(name: 'app_hike_sessions', methods: ['GET'])]
    public function index(HikeSessionRepository $hikeSessionRepository): Response
    {
        return $this->render('hike_session/index.html.twig', [
            'hike_sessions' => $hikeSessionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_session_new', methods: ['GET', 'POST'])]
    public function new(Request $request, HikeRepository $hikeRepo, HikeSessionRepository $sessionRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User $user */
        $user = $this->getUser();

        $session = new HikeSession();

        $form = $this->createFormBuilder($session)
            ->add('hike', EntityType::class, [
                'class' => Hike::class,
                'choice_label' => 'title',
                'placeholder' => 'Choisir une randonnée',
                'query_builder' => fn(HikeRepository $repo) =>
                $repo->createQueryBuilder('h')
                    ->andWhere('h.creator = :user')
                    ->setParameter('user', $user),
            ])
            ->add('date', DateTimeType::class, [
                'label' => 'Date de la session',
                'widget' => 'single_text',
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
                'label' => 'Notes (optionnelles)',
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session->create($user, $session->getHike());
            $sessionRepo->save($session, true);

            $this->addFlash('success', 'Session créée avec succès.');
            return $this->redirectToRoute('app_hike_sessions', ['id' => $session->getHike()->getId()]);
        }

        return $this->render('hike_session/new.html.twig', [
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

            return $this->redirectToRoute('app_hike_sessions', [], Response::HTTP_SEE_OTHER);
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

        return $this->redirectToRoute('app_hike_sessions', [], Response::HTTP_SEE_OTHER);
    }
}
