<?php

namespace App\Controller;

use App\Entity\TimeSlot;
use App\Form\TimeSlotType;
use App\Repository\TimeSlotRepository;
use App\Repository\WorkshopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function new(Request $request, EntityManagerInterface $entityManager, TimeSlotRepository $timeSlotsRepo): Response
    {
        $timeSlot = new TimeSlot();
        $form = $this->createForm(TimeSlotType::class, $timeSlot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($timeSlot->getFromTime() > $timeSlot->getToTime() || $timeSlot->getToTime() < $timeSlot->getFromTime()) {
                $this->addFlash('danger', 'Please check the start and end time again!');
                return $this->redirectToRoute('app_time_slot_index', [], Response::HTTP_SEE_OTHER);
            }

            $slot = $timeSlotsRepo->findBy(['fromTime' => $timeSlot->getFromTime(), 'toTime' => $timeSlot->getToTime(), 'date' => $timeSlot->getDate(), 'workshop' => $timeSlot->getWorkshop()]);
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
        $form = $this->createForm(TimeSlotType::class, $timeSlot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('info', 'The time slot updated successfully!');

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
        $this->addFlash('danger', 'The time slot was deleted!');
        return $this->redirectToRoute('app_time_slot_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/new/batch', name: 'app_time_slot_new_batch')]
    public function newBatch(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Cette structure sert pour l'affichage initial du formulaire.
        // C'est ce qui permet aux valeurs par défaut de TimeSlotType de fonctionner.
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


            // On récupère les données brutes de la requête pour vérifier les cases à cocher.
            $requestData = $request->request->all();
            // Le nom 'workshop_time_slot' est dérivé de WorkshopTimeSlotType
            $dailySlotsRequestData = $requestData['workshop_time_slot']['dailySlots'] ?? [];

            // On boucle sur les données validées du formulaire, en récupérant l'index.
            foreach ($data['dailySlots'] as $index => $dailySlotData) {
                $date = $dailySlotData['date'];

                $morning = $dailySlotData['morningSlot'];
                if ($morning && $morning->getFromTime() && $morning->getToTime()) {
                    $morning->setDate($date);
                    $morning->setWorkshop($workshop);
                    $entityManager->persist($morning);
                }

                // On vérifie si la case 'hasAfternoonSlot' a été envoyée pour cet index.
                // Si la case est décochée, la clé n'existera pas dans les données de la requête.
                if (isset($dailySlotsRequestData[$index]['hasAfternoonSlot'])) {
                    $afternoon = $dailySlotData['afternoonSlot'];
                    if ($afternoon && $afternoon->getFromTime() && $afternoon->getToTime()) {
                        $afternoon->setDate($date);
                        $afternoon->setWorkshop($workshop);
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
