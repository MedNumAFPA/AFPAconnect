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
        $urlHomepage = $this->generateUrl('app_homepage', [], true);

        $qrCodeUrl =$_SERVER['APP_URL'] . $urlHomepage; // Create the QR code with the data
        return $this->render('qr_code/index.html.twig', [
            'controller_name' => 'QrCodeController',
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }

    #[Route('/homepage', name: 'app_qr_code_homepage')]
    public function homepageQrCode(BuilderInterface $builder): Response
    {
        $urlHomepage = $this->generateUrl('app_homepage', [], true);

        $qrCode = new QrCode($_SERVER['APP_URL'] . $urlHomepage); // Create the QR code with the data

        // Generate PNG
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return new Response(
            $result->getString(),
            Response::HTTP_OK,
            ['Content-Type' => $result->getMimeType()]
        );
    }

    #[Route('/visitor', name: 'app_qr_code_visitor')]
    public function visitorQrCode(BuilderInterface $builder): Response
    {
        $urlVisitor = $this->generateUrl('app_homepage_visitor', [], true);

        // Create the QR code with the data
        $qrCode = new QrCode($_SERVER['APP_URL'] . $urlVisitor);

        // Generate PNG
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return new Response(
            $result->getString(),
            Response::HTTP_OK,
            ['Content-Type' => $result->getMimeType()]
        );
    }

    #[Route('/stagiaire', name: 'app_qr_code_intern')]
    public function internQrCode(BuilderInterface $builder): Response
    {
        $urlVisitor = $this->generateUrl('app_homepage_intern', [], true);

        // Create the QR code with the data
        $qrCode = new QrCode($_SERVER['APP_URL'] . $urlVisitor);

        // Generate PNG
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return new Response(
            $result->getString(),
            Response::HTTP_OK,
            ['Content-Type' => $result->getMimeType()]
        );
    }
}
