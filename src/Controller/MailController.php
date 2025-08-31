<?php

namespace App\Controller;

use App\Repository\ReservationsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MailController extends AbstractController
{
//    /**
//     * @throws TransportExceptionInterface
//     */
    #[Route('/mailtest', name: 'app_mail')]
    public function index(MailerInterface $mailer, ReservationsRepository $reservationsRepository): Response
    {




        // 1. Define the time window, exactly like the console command will.
        // This finds workshops scheduled between 24 and 25 hours from now.
        $startTime = new \DateTimeImmutable('+24 hours');
        $endTime = new \DateTimeImmutable('+25 hours');

        // 2. Call the repository method with the calculated time window.
        $reservations = $reservationsRepository->findRemindersDueToSend($startTime, $endTime);

        // 3. Dump the results and stop execution.
        // This is a powerful debugging tool.
        dd($reservations);

        // The code will stop at dd(), so this part will not be reached.
        // This is perfect for testing.
        return $this->render('...');





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
