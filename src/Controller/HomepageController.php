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
        return $this->render('homepage/index.html.twig', [
            'workshops' => $workshopRepository->findAll(),
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
