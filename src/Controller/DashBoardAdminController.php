<?php

namespace App\Controller;

use App\Repository\TimeSlotRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
final class DashBoardAdminController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard_admin')]
    public function dashboard(TimeSlotRepository $timeSlotRepository): Response
    {
        $week = [];
        $weekDates = [];
        $weeks = ['semaine1', 'semaine2', 'semaine3', 'semaine4']; // 4 semaines

        // Exemple : récupérer les dates des jours de la semaine à afficher
        $days = [
            'lundi' => new \DateTime('monday this week'),
            'mardi' => new \DateTime('tuesday this week'),
            'mercredi' => new \DateTime('wednesday this week'),
            'jeudi' => new \DateTime('thursday this week'),
            'vendredi' => new \DateTime('friday this week'),
        ];
        foreach ($weeks as $idx => $weekName) {
            $week[$weekName] = [];
            $weekDates[$weekName] = [];

            foreach ($days as $dayName => $date) {
                // Décaler la date selon la semaine
                $targetDate = (clone $date)->modify('+' . $idx . ' weeks');

                // Récupérer les créneaux pour ce jour déplacé
                $timeSlots = $timeSlotRepository->findSlotsByDate($targetDate);

                $week[$weekName][$dayName] = $timeSlots;
                $weekDates[$weekName][$dayName] = $targetDate;
            }
        }

        // Passez le tableau et seulement les noms des jours pour Twig
        $daysname = array_keys($days);

        return $this->render('dashboard_admin/index.html.twig', [
            'week' => $week,
            'weekDates' => $weekDates,
            'days' => $daysname,
        ]);
    }
}
