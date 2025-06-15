<?php

namespace App\Controller;

use App\Entity\User;
use App\Message\AddPointsToActiveUsers;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('', name: 'admin_user_list')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/user/{id}/toggle', name: 'admin_user_toggle')]
    public function toggle(User $user, EntityManagerInterface $em): RedirectResponse
    {
        $user->setActif(!$user->isActif());
        $em->flush();

        $this->addFlash('success', 'Utilisateur mis à jour avec succès.');

        return $this->redirectToRoute('admin_user_list');
    }

    #[Route('/add-points', name: 'admin_add_points')]
    public function addPoints(MessageBusInterface $bus): RedirectResponse
    {
        $bus->dispatch(new AddPointsToActiveUsers());
        $this->addFlash('success', 'Ajout de points lancé en arrière-plan.');
        return $this->redirectToRoute('admin_user_list');
    }
}
