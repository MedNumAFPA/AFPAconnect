<?php

namespace App\Controller;

use App\Repository\WorkshopRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class HomepageController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(WorkshopRepository $workshopRepository): Response
    {
        $allWorkshops = $workshopRepository->findAll();

        // --- NOUVELLE LOGIQUE ---
        $workshopsData = [];
        foreach ($allWorkshops as $workshop) {
            $isFull = false;
            $remainingSlots = $workshop->getMaxSlots(); // On commence avec la capacité max

            // On vérifie chaque créneau de l'atelier
            foreach ($workshop->getTimeSlots() as $timeSlot) {
                // Le nombre de places restantes pour ce créneau spécifique
                $slotsLeftForThisTimeSlot = $workshop->getMaxSlots() - count($timeSlot->getReservations());

                // Si UN SEUL créneau est plein, on considère l'atelier comme complet pour une souscription de groupe
                if ($slotsLeftForThisTimeSlot <= 0) {
                    $isFull = true;
                }

                // On met à jour le nombre de places restantes avec la valeur la plus basse trouvée
                // C'est le "goulot d'étranglement" de l'atelier
                if ($slotsLeftForThisTimeSlot < $remainingSlots) {
                    $remainingSlots = $slotsLeftForThisTimeSlot;
                }
            }

            // On s'assure que les places restantes ne sont pas négatives
            if ($remainingSlots < 0) {
                $remainingSlots = 0;
            }

            $workshopsData[] = [
                'workshop' => $workshop,
                'is_full' => $isFull,
                'remaining_slots' => $remainingSlots,
            ];
        }

        return $this->render('homepage/index.html.twig', [
            'workshopsData' => $workshopsData,
            'workshops' => $allWorkshops
        ]);
    }

    #[Route('/visitor', name: 'app_homepage_visitor')]
    public function indexVisitor(): Response
    {
        return $this->render('homepage/index-visitor.html.twig', [
            'controller_name' => 'HomepageController',
        ]);
    }

    #[Route('/intern', name: 'app_homepage_intern')]
    public function indexIntern(WorkshopRepository $workshopRepository): Response
    {
        return $this->render('homepage/index-intern.html.twig', [
            'workshops' => $workshopRepository->findAll(),
        ]);
    }
}
