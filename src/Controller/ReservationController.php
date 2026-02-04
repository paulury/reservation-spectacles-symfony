<?php

namespace App\Controller;

use App\Entity\Spectacle;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReservationController extends AbstractController
{
    #[Route('/mes-reservations', name: 'app_mes_reservations')]
    public function index(ReservationRepository $resRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('reservation/index.html.twig', [
            'reservations' => $resRepo->findBy(['user' => $this->getUser()])
        ]);
    }

    #[Route('/spectacle/{id}/reserver', name: 'app_reservation')]
    public function new(Spectacle $spectacle, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quantite = $reservation->getQuantite();

            if ($quantite > $spectacle->getNombrePlace()) {
                $this->addFlash('danger', 'Plus assez de places !');
                return $this->redirectToRoute('app_home');
            }

            $reservation->setSpectacle($spectacle);
            $reservation->setUser($this->getUser());
            $reservation->setPrixTotal($spectacle->getPrix() * $quantite);
            $reservation->setCreatedAt(new \DateTimeImmutable());

            $spectacle->setNombrePlace($spectacle->getNombrePlace() - $quantite);

            $em->persist($reservation);
            $em->flush();

            return $this->redirectToRoute('app_reservation_paiement', ['id' => $reservation->getId()]);
        }

        return $this->render('reservation/new.html.twig', [
            'form' => $form->createView(),
            'spectacle' => $spectacle
        ]);
    }

    #[Route('/reservation/{id}/paiement', name: 'app_reservation_paiement')]
    public function paiement(Reservation $reservation): Response
    {
        if ($reservation->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        return $this->render('reservation/paiement.html.twig', ['res' => $reservation]);
    }
}