<?php

namespace App\Controller;

use App\Entity\TimeSlot;
use App\Form\TimeSlotType;
use App\Repository\TimeSlotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/timeslots')]
final class TimeSlotsController extends AbstractController
{
    #[Route(name: 'app_time_slots_index', methods: ['GET'])]
    public function index(TimeSlotRepository $timeSlotsRepository): Response
    {
        return $this->render('time_slots/index.html.twig', [
            'time_slots' => $timeSlotsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_time_slots_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TimeSlotRepository $timeSlotsRepo): Response
    {
        $timeSlot = new TimeSlot();
        $form = $this->createForm(TimeSlotType::class, $timeSlot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($timeSlot->getFromTime() > $timeSlot->getToTime() || $timeSlot->getToTime() < $timeSlot->getFromTime()) {
                $this->addFlash('danger', 'Please check the start and end time again!');
                return $this->redirectToRoute('app_time_slots_index', [], Response::HTTP_SEE_OTHER);
            }

//            dd($form);

            $slot = $timeSlotsRepo->findBy(['fromTime' => $timeSlot->getFromTime(), 'toTime' => $timeSlot->getToTime(), 'date' => $timeSlot->getDate()]);
            if ($slot) {
                $this->addFlash('danger', 'The time slot already exists!');
                return $this->redirectToRoute('app_time_slots_index', [], Response::HTTP_SEE_OTHER);
            }

            $entityManager->persist($timeSlot);
            $entityManager->flush();
            $this->addFlash('success', 'The time slot  has been added successfully!');
            return $this->redirectToRoute('app_time_slots_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('time_slots/new.html.twig', [
            'time_slot' => $timeSlot,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_time_slots_show', methods: ['GET'])]
    public function show(TimeSlot $timeSlot): Response
    {
        return $this->render('time_slots/show.html.twig', [
            'time_slot' => $timeSlot,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_time_slots_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TimeSlot $timeSlot, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TimeSlotType::class, $timeSlot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('info', 'The time slot updated successfully!');

            return $this->redirectToRoute('app_time_slots_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('time_slots/edit.html.twig', [
            'time_slot' => $timeSlot,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_time_slots_delete')]
    public function delete(TimeSlot $timeSlot, EntityManagerInterface $entityManager): Response
    {
        // dd("test");
        $entityManager->remove($timeSlot);
        $entityManager->flush();
        $this->addFlash('danger', 'The time slot was deleted!');
        return $this->redirectToRoute('app_time_slots_index', [], Response::HTTP_SEE_OTHER);
    }
}
