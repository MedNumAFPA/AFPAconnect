<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomepageController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(): Response
    {
        return $this->render('homepage/index.html.twig', [
            'controller_name' => 'HomepageController',
        ]);
    }

    #[Route('/visitor', name: 'app_homepage_visitor')]
    public function indexVisitor(): Response
    {
        return $this->render('homepage/index-visitor.html.twig', [
            'controller_name' => 'HomepageController',
        ]);
    }

    #[Route('/stagiaire', name: 'app_homepage_intern')]
    public function indexIntern(): Response
    {
        return $this->render('homepage/index-intern.html.twig', [
            'controller_name' => 'HomepageController',
        ]);
    }
}
