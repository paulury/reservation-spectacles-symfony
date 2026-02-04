<?php

namespace App\Controller\Admin;

use App\Repository\SpectacleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class StatsController extends AbstractController
{
    #[Route('/admin/stats', name: 'admin_stats')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(SpectacleRepository $spectacleRepository): Response
    {
        $spectacles = $spectacleRepository->findAll();
        $stats = [];

        foreach ($spectacles as $s) {
            $nbPlacesReservees = 0;
            $totalArgent = 0;

            foreach ($s->getReservations() as $r) {
                $nbPlacesReservees += $r->getQuantite();
                $totalArgent += $r->getPrixTotal();
            }

            $stats[] = [
                'titre' => $s->getTitre(),
                'places' => $nbPlacesReservees,
                'total' => $totalArgent
            ];
        }

        return $this->render('admin/stats/index.html.twig', [
            'stats' => $stats,
        ]);
    }
}