<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashBoardAdminController extends AbstractController
{
    #[Route('/dashboardadmin', name: 'app_dashboard_admin')]
    public function index(): Response
    {
        return $this->render('dashboard_admin/index.html.twig', [
            'controller_name' => 'DashBoardAdminController',
        ]);
    }
}
