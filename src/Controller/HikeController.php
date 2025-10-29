<?php

namespace App\Controller;

use App\Entity\Hike;
use App\Form\HikeType;
use App\Repository\HikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/hike')]
final class HikeController extends AbstractController
{

    #[Route(name: 'app_hike_index', methods: ['GET'])]
    public function index(HikeRepository $hikeRepository): Response
    {
        $user = $this->getUser();
        $hikes = $hikeRepository->findBy(['creator' => $user]);

        return $this->render('hike/index.html.twig', [
            'hikes' => $hikes,
        ]);
    }

    #[Route('/new', name: 'app_hike_new', methods: ['GET', 'POST'])]
    public function new(Request $request, HikeRepository $hikeRepository): Response
    {
        $hike = new Hike();
        $form = $this->createForm(HikeType::class, $hike);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
    public function show(Hike $hike): Response
    {
        return $this->render('hike/show.html.twig', [
            'hike' => $hike,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_hike_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Hike $hike, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HikeType::class, $hike);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
}
