<?php

namespace App\Controller;

use App\Entity\Workshop;
use App\Form\WorkshopType;
use App\Repository\WorkshopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/workshops')]
final class WorkshopController extends AbstractController
{
    #[Route(name: 'app_workshops_index', methods: ['GET'])]
    public function index(WorkshopRepository $workshopsRepository): Response
    {
        return $this->render('workshops/index.html.twig', [
            'workshops' => $workshopsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_workshops_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $workshop = new Workshop();
        $form = $this->createForm(WorkshopType::class, $workshop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($workshop);
            $entityManager->flush();
            $this->addFlash('success', 'The workshop added successfully!');

            return $this->redirectToRoute('app_workshops_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('workshops/new.html.twig', [
            'workshop' => $workshop,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_workshops_show', methods: ['GET'])]
    public function show(Workshop $workshop): Response
    {
        return $this->render('workshops/show.html.twig', [
            'workshop' => $workshop,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_workshops_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Workshop $workshop, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WorkshopType::class, $workshop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('info', 'The workshop updated successfully!');
            return $this->redirectToRoute('app_workshops_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('workshops/edit.html.twig', [
            'workshop' => $workshop,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_workshops_delete')]
    public function delete(Request $request, Workshop $workshop, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $workshop->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($workshop);
            $entityManager->flush();
            $this->addFlash('danger', 'The workshop deleted successfully!');
        }

        return $this->redirectToRoute('app_workshops_index', [], Response::HTTP_SEE_OTHER);
    }
}