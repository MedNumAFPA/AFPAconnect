<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Workshop;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use App\Repository\WorkshopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    #[Route('/admin/reservation', name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationsRepository): Response
    {
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationsRepository->findAll(),
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

    /**
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route('/workshop/{id}/subscribe', name: 'app_workshop_subscribe')]
    public function subscribeToWorkshop(
        Workshop               $workshop,
        Request                $request,
        EntityManagerInterface $entityManager,
        Environment            $twig,
        KernelInterface        $kernel,
        MailerInterface        $mailer,
    ): Response
    {
        $reservationTemplate = new Reservation();

        $form = $this->createForm(ReservationType::class, $reservationTemplate, [
            'is_workshop_subscription' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $timeSlots = $workshop->getTimeSlots();

            if ($timeSlots->isEmpty()) {
                $this->addFlash('danger', 'Cet atelier n\'a pas de créneaux disponibles.');
                return $this->redirectToRoute('app_homepage');
            }

            foreach ($timeSlots as $timeSlot) {

                $newReservation = clone $reservationTemplate;
                $newReservation->setTimeslot($timeSlot);
                $entityManager->persist($newReservation);

            }

            $htmlContent = $twig->render('email/subscription.html.twig', [
                'reservation' => $newReservation,
            ]);

            $email = (new Email())
                ->from('Afpa Connect <subscribed@afpaconnect.fr>') // Change this later
                ->to($newReservation->getEmail())
                ->subject('Vous avez été inscrit â un atelier !')
//            ->embedFromPath('public/images/logo.png', 'logo')
                ->embedFromPath(
                    $kernel->getProjectDir()
                    .
                    '/public/images/logo-txt.png', 'logo-txt')
                ->text('This is the plain text version of the email.') // Fallback plain text
                // Set the HTML body of the email
                ->html($htmlContent);

            $mailer->send($email);

            $entityManager->flush();
            $this->addFlash('success', 'Votre inscription à l\'atelier "' . $workshop->getName() . '" a été enregistrée avec succès !');
            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('reservation/subscribe.html.twig', [
            'workshop' => $workshop,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/rendez-vous', name: 'app_rendez_vous_new')]
    public function newRendezVous(
        Request                $request,
        EntityManagerInterface $entityManager,
        WorkshopRepository     $workshopRepository
    ): Response
    {
        $reservation = new Reservation();

        // 1. Find the specific "Entretien Individuel" workshop.
        // We find it by a unique, hardcoded name.
        $entretienWorkshop = $workshopRepository->findOneBy(['name' => 'Entretien Individuel']);

        if (!$entretienWorkshop) {
            // Handle the case where the admin hasn't created the workshop yet.
            $this->addFlash('warning', 'Les entretiens individuels ne sont pas disponibles pour le moment.');
            return $this->redirectToRoute('app_homepage');
        }

//dd($reservation);

        // 2. Pass the workshop ID to the form options.
        $form = $this->createForm(ReservationType::class, $reservation, [
            'workshop_id' => $entretienWorkshop->getId(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
