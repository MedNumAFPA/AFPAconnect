<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
#[\Symfony\Component\Routing\Attribute\Route('/admin')]
class MailController extends AbstractController
{
//    /**
//     * @throws TransportExceptionInterface
//     */
    #[Route('/mailtest', name: 'app_mail')]
    public function index(MailerInterface $mailer, ReservationRepository $reservationsRepository, KernelInterface $kernel): Response
    {




        // Define the time window, exactly like the console command will.
        // This finds workshop scheduled between 23 and 25 hours from now.
        $startTime = new \DateTimeImmutable('+23 hours');
        $endTime = new \DateTimeImmutable('+25 hours');

        $reservations = $reservationsRepository->findRemindersDueToSend($startTime, $endTime);

        dd($startTime, $endTime, $reservations, $kernel->getProjectDir());



//        $email = (new Email())
//            ->from('me@arnaudrabel.com')
//            ->to('arnaud.rabel@gmail.com')
////            ->to('amine.elkhal@icloud.com')
////            ->to('shamashan@gmail.com')
////            ->to('mounir.sebti33@gmail.com')
//            ->subject('Test Email from Symfony!')
//            ->text('This is the plain text version of the email.')
//            ->html('<p>This is the <strong>HTML</strong> version of the email. If you see this, it worked!</p>');
//
//        $mailer->send($email);
//
//        return new Response('Test email has been sent! Check your inbox.');
    }
}
