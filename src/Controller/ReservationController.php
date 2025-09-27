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
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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

    /**
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     * @throws ORMException
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route('/workshop/{id}/subscribe', name: 'app_workshop_subscribe')]
    public function subscribeToWorkshop(
        Workshop               $workshop,
        Request                $request,
        EntityManagerInterface $entityManager,
        ReservationRepository  $reservationRepository,
        Environment            $twig,
        KernelInterface        $kernel,
        MailerInterface        $mailer,
        Packages               $assets,
        UriSigner              $uriSigner
    ): Response
    {
        $availableTimeSlots = $workshop->getTimeSlots()->filter(function ($timeSlot) use ($workshop) {
            return $timeSlot->getDate() > new \DateTime('today') &&
                count($timeSlot->getReservations()) < $workshop->getMaxSlots();
        });

        if ($availableTimeSlots->isEmpty()) {
            $this->addFlash('warning', sprintf('L\'atelier "%s" n\'a plus de créneaux disponibles.', $workshop->getName()));
            return $this->redirectToRoute('app_homepage');
        }

        $reservationTemplate = new Reservation();

        $form = $this->createForm(ReservationType::class, $reservationTemplate, [
            'is_workshop_subscription' => true,
            'workshop_id' => $workshop->getId(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $selectedTimeSlots = $form->get('timeslots')->getData()->toArray();

            if (empty($selectedTimeSlots)) {
                $form->get('timeslots')->addError(new FormError('Vous devez sélectionner au moins un créneau pour vous inscrire.'));
            }

            if (!empty($selectedTimeSlots)) {
                $duplicateReservations = $reservationRepository->findExistingReservationsForTimeslots(
                    $reservationTemplate->getEmail(),
                    $selectedTimeSlots
                );

                if (!empty($duplicateReservations)) {
                    $duplicateDetails = [];
                    foreach ($duplicateReservations as $dup) {
                        $timeslot = $dup->getTimeslot();
                        $duplicateDetails[] = sprintf(
                            '%s (%s - %s)',
                            $timeslot->getDate()->format('d/m/Y'),
                            $timeslot->getFromTime()->format('H:i'),
                            $timeslot->getToTime()->format('H:i')
                        );
                    }

                    $form->get('timeslots')->addError(new FormError(
                        'Vous êtes déjà inscrit(e) pour le(s) créneau(x) suivant(s) :',
                        null, // No message template
                        ['%duplicates%' => array_unique($duplicateDetails)] // Pass the array here
                    ));
                }
            }

            if ($form->isValid()) {
                $createdReservations = [];
                foreach ($selectedTimeSlots as $timeSlot) {
                    $entityManager->refresh($timeSlot);

                    if (count($timeSlot->getReservations()) >= $timeSlot->getWorkshop()->getMaxSlots()) {
                        $this->addFlash('danger', sprintf('Le créneau du %s à %s est maintenant complet. Veuillez essayer de nouveau.', $timeSlot->getDate()->format('d/m/Y'), $timeSlot->getFromTime()->format('H:i')));
                        return $this->redirectToRoute('app_workshop_subscribe', ['id' => $workshop->getId()]);
                    }

                    $newReservation = clone $reservationTemplate;
                    $newReservation->setTimeslot($timeSlot);
                    $entityManager->persist($newReservation);
                    $createdReservations[] = $newReservation;
                }

                $entityManager->flush();

                $unsubscribeUrl = $this->generateUrl('app_workshop_unsubscribe_confirm', [
                    'workshopId' => $workshop->getId(),
                    'email' => $reservationTemplate->getEmail(),
                ], UrlGeneratorInterface::ABSOLUTE_URL);

                $signedUnsubscribeUrl = $uriSigner->sign($unsubscribeUrl);

                $reservationsByDate = [];
                foreach ($createdReservations as $reservation) {
                    $dateKey = $reservation->getTimeSlot()->getDate()->format('Y-m-d');
                    $reservationsByDate[$dateKey][] = $reservation;
                }

                $htmlContent = $twig->render('email/subscription.html.twig', [
                    'reservations' => $createdReservations,
                    'reservationsByDate' => $reservationsByDate,
                    'signedUnsubscribeUrl' => $signedUnsubscribeUrl,
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
        }

        $responseStatusCode = ($form->isSubmitted() && !$form->isValid())
            ? Response::HTTP_UNPROCESSABLE_ENTITY
            : Response::HTTP_OK;

        return $this->render('reservation/subscribe.html.twig', [
            'workshop' => $workshop,
            'form' => $form->createView(),
        ], new Response(null, $responseStatusCode));
    }

    #[Route('/workshop/unsubscribe', name: 'app_workshop_unsubscribe_confirm', methods: ['GET'])]
    public function unsubscribeConfirm(Request $request, UriSigner $uriSigner): Response
    {

        $fullUrl = $request->getUri();

        // Check the signature of the full URL.
        if (!$uriSigner->check($fullUrl)) {
            $this->addFlash('danger', 'Le lien de désinscription est invalide ou a expiré.');
            return $this->redirectToRoute('app_homepage');
        }

        $request->getSession()->set('unsubscribe_url', $fullUrl);

        return $this->render('reservation/unsubscribe_confirm.html.twig');
    }

    #[Route('/workshop/unsubscribe-handle', name: 'app_workshop_unsubscribe_handle', methods: ['POST'])]
    public function unsubscribeHandle(
        Request $request,
        ReservationRepository $reservationRepository,
        EntityManagerInterface $entityManager
    ): Response
    {

        $session = $request->getSession();
        $signedUrl = $session->get('unsubscribe_url');

        $session->remove('unsubscribe_url');

        // If there's no URL in the session, the user got here improperly.
        if (null === $signedUrl) {
            $this->addFlash('danger', 'Session de désinscription invalide. Veuillez utiliser le lien de votre email.');
            return $this->redirectToRoute('app_homepage');
        }

        // Manually parse the query string from the VALID signed URL.
        $queryParams = [];
        parse_str((string)parse_url($signedUrl, PHP_URL_QUERY), $queryParams);

        $workshopId = $queryParams['workshopId'] ?? null;
        $email = $queryParams['email'] ?? null;

        if ($workshopId && $email) {
            $reservationsToDelete = $reservationRepository->findByEmailAndWorkshop($email, (int)$workshopId);

            if (empty($reservationsToDelete)) {
                $this->addFlash('warning', 'Votre inscription a déjà été annulée.');
            } else {
                foreach ($reservationsToDelete as $reservation) {
                    $entityManager->remove($reservation);
                }
                $entityManager->flush();
                $this->addFlash('success', 'Votre inscription a été annulée avec succès.');
            }
        }

        return $this->redirectToRoute('app_homepage');
    }

    /**
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route('/rendez-vous', name: 'app_rendez_vous_new')]
    public function newRendezVous(
        Request                $request,
        EntityManagerInterface $entityManager,
        WorkshopRepository     $workshopRepository,
        TimeSlotRepository     $timeSlotRepository,
        MailerInterface        $mailer,
        KernelInterface        $kernel,
        Packages               $assets,
        Environment            $twig,
        UriSigner              $uriSigner,
    ): Response
    {
        $reservation = new Reservation();
        $entretienWorkshop = $workshopRepository->findOneBy(['name' => 'Entretien Individuel']);

        $form = $this->createForm(ReservationType::class, $reservation, [
            'workshop_id' => $entretienWorkshop->getId(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($reservation);
            $entityManager->flush();

            $unsubscribeUrl = $this->generateUrl('app_rendezvous_unsubscribe_confirm', [
                'id' => $reservation->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            // Sign the URL to make it tamper-proof
            $signedUnsubscribeUrl = $uriSigner->sign($unsubscribeUrl);

            $htmlContent = $twig->render('email/rendez_vous_confirmation.html.twig', [
                'reservation' => $reservation,
                'signedUnsubscribeUrl' => $signedUnsubscribeUrl,
            ]);

            $email = (new Email())
                ->from('Afpa Connect <rendezvous@afpaconnect.fr>')
                ->to($reservation->getEmail())
                ->subject('Confirmation de votre Entretien Individuel')
                ->embedFromPath($kernel->getProjectDir() . '/public' . $assets->getUrl('images/logo-txt.png'), 'logo-txt')
                ->html($htmlContent);

            $mailer->send($email);

            $this->addFlash('success', 'Votre rendez-vous a été enregistré avec succès ! Un email de confirmation vous a été envoyé.');
            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('reservation/new_rendez_vous.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/rendez-vous/{id}/unsubscribe', name: 'app_rendezvous_unsubscribe_confirm', methods: ['GET'])]
    public function unsubscribeRendezVousConfirm(Request $request, UriSigner $uriSigner, Reservation $reservation): Response
    {
        // The check uses the full URL the user arrived with.
        if (!$uriSigner->check($request->getUri())) {
            $this->addFlash('danger', 'Le lien d\'annulation est invalide ou a expiré.');
            return $this->redirectToRoute('app_homepage');
        }

        // Store the reservation ID in the session for the next step.
        $request->getSession()->set('unsubscribe_rdv_id', $reservation->getId());

        // Render the confirmation page.
        return $this->render('reservation/unsubscribe_rendezvous_confirm.html.twig');
    }

    /**
     * Handles the actual cancellation after confirmation.
     */
    #[Route('/rendez-vous/unsubscribe-handle', name: 'app_rendezvous_unsubscribe_handle', methods: ['POST'])]
    public function unsubscribeRendezVousHandle(Request $request, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository): Response
    {
        $session = $request->getSession();
        $reservationId = $session->get('unsubscribe_rdv_id');

        // Remove the ID from the session immediately so it can only be used once.
        $session->remove('unsubscribe_rdv_id');

        if (null === $reservationId) {
            $this->addFlash('danger', 'Session d\'annulation invalide. Veuillez utiliser le lien de votre email.');
            return $this->redirectToRoute('app_homepage');
        }

        $reservation = $reservationRepository->find($reservationId);

        if (!$reservation) {
            $this->addFlash('warning', 'Ce rendez-vous a déjà été annulé.');
        } else {
            $entityManager->remove($reservation);
            $entityManager->flush();
            $this->addFlash('success', 'Votre rendez-vous a été annulé avec succès.');
        }

        return $this->redirectToRoute('app_homepage');
    }


    #[Route('/subscription/{id}/delete', name: 'app_reservation_subscription_delete', methods: ['POST'])]
    public function deleteSubscription(
        Request $request,
        Reservation $mainReservation,
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository
    ): Response
    {
        // Security: Check for the CSRF token from the form
        if ($this->isCsrfTokenValid('delete_subscription'.$mainReservation->getId(), (string)$request->request->get('_token'))) {

            // Find all reservations for this person for this workshop
            $workshopId = $mainReservation->getTimeSlot()->getWorkshop()->getId();
            $email = $mainReservation->getEmail();

            $reservationsToDelete = $reservationRepository->findByEmailAndWorkshop($email, $workshopId);

            foreach ($reservationsToDelete as $reservation) {
                $entityManager->remove($reservation);
            }

            $entityManager->flush();

            $this->addFlash('danger', sprintf(
                'L\'inscription de "%s %s" à l\'atelier a été supprimée avec succès.',
                $mainReservation->getFirstname(),
                $mainReservation->getLastname()
            ));
        }

        return $this->redirectToRoute('app_reservation_index');
    }
}
