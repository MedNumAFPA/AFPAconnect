<?php

namespace App\Controller;

use App\Repository\TimeSlotRepository;
use App\Repository\WorkshopRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class HomepageController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(WorkshopRepository $workshopRepository, TimeSlotRepository $timeSlotRepository): Response
    {
        $allWorkshops = $workshopRepository->findAll();

        $workshopsData = [];
        foreach ($allWorkshops as $workshop) {
            $isFull = false;
            $remainingSlots = $workshop->getMaxSlots();

            foreach ($workshop->getTimeSlots() as $timeSlot) {

                $slotsLeftForThisTimeSlot = $workshop->getMaxSlots() - count($timeSlot->getReservations());

                if ($slotsLeftForThisTimeSlot <= 0) {
                    $isFull = true;
                }
                if ($slotsLeftForThisTimeSlot < $remainingSlots) {
                    $remainingSlots = $slotsLeftForThisTimeSlot;
                }
            }

            if ($remainingSlots < 0) {
                $remainingSlots = 0;
            }

            $workshopsData[] = [
                'workshop' => $workshop,
                'is_full' => $isFull,
                'remaining_slots' => $remainingSlots,
            ];
        }

        $isRendezVousAvailable = false;
        $entretienWorkshop = $workshopRepository->findOneBy(['name' => 'Entretien Individuel']);

        if ($entretienWorkshop) {

            $availableSlotsCount = $timeSlotRepository->countAvailableByWorkshop($entretienWorkshop->getId());
            if ($availableSlotsCount > 0) {
                $isRendezVousAvailable = true;
            }
        }

        return $this->render('homepage/index.html.twig', [
            'workshopsData' => $workshopsData,
            'workshops' => $allWorkshops,
            'isRendezVousAvailable' => $isRendezVousAvailable,
        ]);
    }
}
