<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Workshop;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use App\Repository\TimeSlotRepository;
use App\Repository\WorkshopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;


final class ReservationController extends AbstractController
{
    #[Route('/admin/reservation', name: 'app_reservation_index')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        $allReservations = $reservationRepository->findBy([], ['id' => 'DESC']);

        $groupedSubscriptions = [];
        foreach ($allReservations as $reservation) {
            $workshop = $reservation->getTimeslot()->getWorkshop();

            // Use the person's email and the workshop ID for a stable, unique key
            $key = $reservation->getEmail() . '-' . $workshop->getId();

            if (!isset($groupedSubscriptions[$key])) {
                $groupedSubscriptions[$key] = [
                    'main_reservation' => $reservation,
                    'workshop' => $workshop,
                    'individual_reservations' => [],
                ];
            }
            // Add the full reservation object to the group
            $groupedSubscriptions[$key]['individual_reservations'][] = $reservation;
        }

        return $this->render('reservation/index.html.twig', [
            'subscriptions' => $groupedSubscriptions,
            'reservations' => $reservationRepository->findAll(),
        ]);
    }



    #[Route('/admin/reservation/new', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Réservation ajoutée avec succès !');
            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/admin/reservation/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/admin/reservation/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('info', 'Réservation modifiée avec succès !');

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/admin/reservation/{id}/delete', name: 'app_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
            $this->addFlash('danger', 'Réservation supprimée avec succès !');
        }
        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/workshop/{id}/subscribe', name: 'app_workshop_subscribe')]
    public function subscribeToWorkshop(
        Workshop               $workshop,
        Request                $request,
        EntityManagerInterface $entityManager,
        Environment            $twig,
        KernelInterface        $kernel,
        MailerInterface        $mailer,
        Packages               $assets,
    ): Response
    {
        $reservationTemplate = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservationTemplate, [
            'is_workshop_subscription' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $timeSlots = $workshop->getTimeSlots();
            $maxSlots = $workshop->getMaxSlots();

            if ($timeSlots->isEmpty()) {
                $this->addFlash('danger', 'Cet atelier n\'a pas de créneaux disponibles.');
                return $this->redirectToRoute('app_homepage');
            }

            foreach ($timeSlots as $timeSlot) {

                if (count($timeSlot->getReservations()) >= $maxSlots) {
                    $this->addFlash('danger', sprintf('Désolé, l\'atelier "%s" est complet.', $workshop->getName()));
                    return $this->redirectToRoute('app_homepage');
                }
            }

            $createdReservations = [];
            foreach ($timeSlots as $timeSlot) {
                $newReservation = clone $reservationTemplate;
                $newReservation->setTimeslot($timeSlot);
                $entityManager->persist($newReservation);
                $createdReservations[] = $newReservation;
            }

            $entityManager->flush();

            $reservationsByDate = [];
            foreach ($createdReservations as $reservation) {
                $dateKey = $reservation->getTimeSlot()->getDate()->format('Y-m-d');
                $reservationsByDate[$dateKey][] = $reservation;
            }

            $htmlContent = $twig->render('email/subscription.html.twig', [
                'reservations' => $createdReservations,
                'reservationsByDate' => $reservationsByDate,
            ]);

            $email = (new Email())
                ->from('Afpa Connect <subscribed@afpaconnect.fr>')
                ->to($reservationTemplate->getEmail())
                ->subject('Confirmation de votre inscription à l\'atelier : ' . $workshop->getName())
                ->embedFromPath($kernel->getProjectDir() . '/public' . $assets->getUrl('images/logo-txt.png'), 'logo-txt')
                ->html($htmlContent);

            $mailer->send($email);

            $this->addFlash('success', 'Votre inscription à l\'atelier "' . $workshop->getName() . '" a été enregistrée avec succès !');
            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('reservation/subscribe.html.twig', ['workshop' => $workshop,
            'form' => $form->createView(),]);
    }

    /**
     * @throws ORMException
     */
    #[Route('/rendez-vous', name: 'app_rendez_vous_new')]
    public function newRendezVous(
        Request                $request,
        EntityManagerInterface $entityManager,
        WorkshopRepository     $workshopRepository,
        TimeSlotRepository     $timeSlotRepository,
    ): Response
    {
        $reservation = new Reservation();
        // We find the specific "Entretien Individuel" workshop by a unique, hardcoded name.
        $entretienWorkshop = $workshopRepository->findOneBy(['name' => 'Entretien Individuel']);

        if (!$entretienWorkshop) {
            $this->addFlash('warning', 'Les entretiens individuels ne sont pas disponibles pour le moment.');
            return $this->redirectToRoute('app_homepage');
        }

        $availableSlotsCount = $timeSlotRepository->countAvailableByWorkshop($entretienWorkshop->getId());

        if ($availableSlotsCount <= 0) {
            $this->addFlash('warning', 'Désolé, il n\'y a plus de créneaux disponibles pour un entretien individuel.');
            return $this->redirectToRoute('app_homepage');
        }

        $form = $this->createForm(ReservationType::class, $reservation, [
            'workshop_id' => $entretienWorkshop->getId(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedTimeSlot = $reservation->getTimeslot();
            $entityManager->refresh($selectedTimeSlot);
            if (!$selectedTimeSlot->getReservations()->isEmpty()) {
                $this->addFlash('danger', 'Le créneau que vous avez choisi vient d\'être réservé. Veuillez en sélectionner un autre.');

                return $this->render('reservation/new_rendez_vous.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Votre rendez-vous a été enregistré avec succès !');
            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('reservation/new_rendez_vous.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
