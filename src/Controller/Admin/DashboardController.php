<?php

namespace App\Controller\Admin;

use App\Entity\Spectacle;
use App\Entity\Reservation;
use App\Entity\User;
use App\Repository\ReservationRepository;
use App\Repository\SpectacleRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{
    private $reservationRepository;
    private $spectacleRepository;

    public function __construct(ReservationRepository $reservationRepository, SpectacleRepository $spectacleRepository)
    {
        $this->reservationRepository = $reservationRepository;
        $this->spectacleRepository = $spectacleRepository;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // RÉCUPÉRATION DE L'UTILISATEUR CONNECTÉ
        $user = $this->getUser();

        // INITIALISATION DU COMPTE ADMIN DANS LE CODE
        // Remplace l'email ci-dessous par le tien
        $adminEmail = "admin@admin.fr";

        // SI L'UTILISATEUR N'EST PAS CONNECTÉ OU N'A PAS LE BON EMAIL -> ACCÈS REFUSÉ
        if (!$user || $user->getUserIdentifier() !== $adminEmail) {
            throw $this->createAccessDeniedException("Seul l'administrateur système ($adminEmail) peut accéder à cette page.");
        }

        $allReservations = $this->reservationRepository->findAll();
        $allSpectacles = $this->spectacleRepository->findAll();
        
        $totalCA = 0;
        $totalTickets = 0;
        $statsParSpectacle = [];

        foreach ($allSpectacles as $spectacle) {
            $statsParSpectacle[$spectacle->getId()] = [
                'titre' => $spectacle->getTitre(),
                'tickets' => 0,
                'ca' => 0,
                'stock' => $spectacle->getNombrePlace()
            ];
        }

        foreach ($allReservations as $res) {
            $totalCA += $res->getPrixTotal();
            $totalTickets += $res->getQuantite();
            
            $specId = $res->getSpectacle()->getId();
            if (isset($statsParSpectacle[$specId])) {
                $statsParSpectacle[$specId]['tickets'] += $res->getQuantite();
                $statsParSpectacle[$specId]['ca'] += $res->getPrixTotal();
            }
        }

        return $this->render('admin/dashboard.html.twig', [
            'totalCA' => $totalCA,
            'totalTickets' => $totalTickets,
            'countReservations' => count($allReservations),
            'statsParSpectacle' => $statsParSpectacle,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()->setTitle('SpectacleApp - Espace Privé');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-chart-line');
        yield MenuItem::linkToCrud('Spectacles', 'fas fa-theater-masks', Spectacle::class);
        yield MenuItem::linkToCrud('Réservations', 'fas fa-ticket-alt', Reservation::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);
        yield MenuItem::linkToRoute('Retour au site', 'fas fa-arrow-left', 'app_home');
    }
}