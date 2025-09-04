<?php

namespace App\Controller;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Builder\BuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/qr-code')]
final class QrCodeController extends AbstractController
{
    #[Route('/', name: 'app_qr_code')]
    public function index(): Response
    {
        return $this->render('qr_code/index.html.twig', [
            'controller_name' => 'QrCodeController',
        ]);
    }

    #[Route('/reservation', name: 'app_qr_code_reservation')]
    public function reservationQrCode(BuilderInterface $builder): Response
    {
        $urlReservation = $this->generateUrl('app_qr_code_reservation-template', [], true);

        $qrCode = new QrCode($urlReservation); // Create the QR code with the data
        $qrCode->getSize(500);
        $qrCode->getMargin(10);

        // Génère l'image PNG
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return new Response(
            $result->getString(),
            Response::HTTP_OK,
            ['Content-Type' => $result->getMimeType()]
        );
    }
    #[Route('/reservation-template', name: 'app_qr_code_reservation-template')]
    public function reservation(): Response
    {
        // logique de la page de réservation
//        return $this->render('qr_code/reservation.html.twig');
    }
}


// #[Route('/qr-code/reservation', name: 'qr_code_reservation')]
// public function reservationQrCode(BuilderInterface $qrCodeBuilder): Response
// {
//     $urlReservation = $this->generateUrl('reservation_page', [], true);

//     $result = $qrCodeBuilder
//         // ->data($urlReservation)  // Contenu du QR code
//         // ->size(300)             // Taille du QR code
//         // ->margin(10)            // Marge blanche
//         // ->margin(10)            // Marge blanche
//         ->build();

//     return new QrCodeResponse($result);

// }
// #[Route('/reservation', name: 'reservation_page')]
// public function reservation()
// {
//     // logique de la page de réservation
//     return $this->render('reservation/page.html.twig');
// }
