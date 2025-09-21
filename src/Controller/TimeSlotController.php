<?php

namespace App\Controller;

use App\Entity\TimeSlot;
use App\Form\TimeSlotType;
use App\Repository\TimeSlotRepository;
use App\Repository\WorkshopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\WorkshopTimeSlotType;

#[Route('/admin/timeslots')]
final class TimeSlotController extends AbstractController
{
    #[Route(name: 'app_time_slot_index', methods: ['GET'])]
    public function index(WorkshopRepository $workshopRepository): Response
    {
        return $this->render('time_slot/index.html.twig', [
            'workshops' => $workshopRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_time_slot_new', methods: ['GET', 'POST'])]
    public function new(
        Request                $request,
        EntityManagerInterface $entityManager,
        TimeSlotRepository     $timeSlotsRepo
    ): Response
    {
        $timeSlot = new TimeSlot();
        $form = $this->createForm(TimeSlotType::class, $timeSlot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($timeSlot->getToTime() < $timeSlot->getFromTime()) {
                $this->addFlash('danger', 'Please check the start and end time again!');
                return $this->redirectToRoute('app_time_slot_index', [], Response::HTTP_SEE_OTHER);
            }

            $slot = $timeSlotsRepo->findBy(
                [
                    'fromTime' => $timeSlot->getFromTime(),
                    'toTime' => $timeSlot->getToTime(),
                    'date' => $timeSlot->getDate(),
                    'workshop' => $timeSlot->getWorkshop()
                ]);
            if ($slot) {
                $this->addFlash('danger', 'The time slot already exists!');
                return $this->redirectToRoute('app_time_slot_index', [], Response::HTTP_SEE_OTHER);
            }

            $entityManager->persist($timeSlot);
            $entityManager->flush();
            $this->addFlash('success', 'The time slot  has been added successfully!');
            return $this->redirectToRoute('app_time_slot_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('time_slot/new.html.twig', [
            'time_slot' => $timeSlot,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_time_slots_show', methods: ['GET'])]
    public function show(TimeSlot $timeSlot): Response
    {
        return $this->render('time_slot/show.html.twig', [
            'time_slot' => $timeSlot,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_time_slot_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TimeSlot $timeSlot, EntityManagerInterface $entityManager): Response
    {
        $isEntretienIndividuel = false;

        if ($timeSlot->getWorkshop() && $timeSlot->getWorkshop()->getName() === 'Entretien Individuel') {
            $isEntretienIndividuel = true;
        }

        $form = $this->createForm(TimeSlotType::class, $timeSlot, [
            'is_entretien_individuel' => $isEntretienIndividuel,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('info', 'Le créneau a été modifié avec succès.');

            return $this->redirectToRoute('app_time_slot_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('time_slot/edit.html.twig', [
            'time_slot' => $timeSlot,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_time_slot_delete')]
    public function delete(TimeSlot $timeSlot, EntityManagerInterface $entityManager): Response
    {
        // dd("test");
        $entityManager->remove($timeSlot);
        $entityManager->flush();
        $this->addFlash('danger', 'Le créneau a été supprimé avec succès.');
        return $this->redirectToRoute('app_time_slot_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/new/batch', name: 'app_time_slot_new_batch')]
    public function newBatch(Request $request, EntityManagerInterface $entityManager): Response
    {
        $initialData = [
            'dailySlots' => [
                ['date' => new \DateTime(),]
            ]
        ];

        $form = $this->createForm(WorkshopTimeSlotType::class, $initialData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $workshop = $data['workshop'];

            // --- START: NEW LOGIC ---
            // 1. Get the assigned users from the unmapped form field.
            $assignedUsers = $form->get('users')->getData();

            // 2. Perform the conditional validation.
            if ($workshop && $workshop->getName() === 'Entretien Individuel' && $assignedUsers->isEmpty()) {
                // Add an error to the 'users' field and re-render the form.
                $form->get('users')->addError(new FormError('Vous devez assigner au moins un animateur pour un entretien individuel.'));

                return $this->render('time_slot/new_batch.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
            // --- END: NEW LOGIC ---

            $requestData = $request->request->all();
            $dailySlotsRequestData = $requestData['workshop_time_slot']['dailySlots'] ?? [];

            foreach ($data['dailySlots'] as $index => $dailySlotData) {
                $date = $dailySlotData['date'];

                $morning = $dailySlotData['morningSlot'];
                if ($morning && $morning->getFromTime() && $morning->getToTime()) {
                    $morning->setDate($date);
                    $morning->setWorkshop($workshop);

                    // --- START: NEW LOGIC ---
                    // 3. Assign the selected users to the morning timeslot.
                    foreach ($assignedUsers as $user) {
                        $morning->addUser($user);
                    }
                    // --- END: NEW LOGIC ---

                    $entityManager->persist($morning);
                }

                if (isset($dailySlotsRequestData[$index]['hasAfternoonSlot'])) {
                    $afternoon = $dailySlotData['afternoonSlot'];
                    if ($afternoon && $afternoon->getFromTime() && $afternoon->getToTime()) {
                        $afternoon->setDate($date);
                        $afternoon->setWorkshop($workshop);

                        // --- START: NEW LOGIC ---
                        // 4. Assign the same users to the afternoon timeslot.
                        foreach ($assignedUsers as $user) {
                            $afternoon->addUser($user);
                        }
                        // --- END: NEW LOGIC ---

                        $entityManager->persist($afternoon);
                    }
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Les créneaux ont été ajoutés avec succès !');
            return $this->redirectToRoute('app_time_slot_index');
        }

        return $this->render('time_slot/new_batch.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
