<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Notification;
use App\Form\ProduitForm;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/produit')]
final class ProduitController extends AbstractController
{
    #[Route(name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitForm::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $produit->setUser($this->getUser());
            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitForm::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Notification admin
            $notification = new Notification();
            $notification->setLabel('Le produit "' . $produit->getNom() . '" a été modifié par un admin.');
            $notification->setUser($this->getAdminUser($entityManager));
            $entityManager->persist($notification);
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $produit->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/acheter', name: 'app_produit_acheter', methods: ['POST'])]
    public function acheter(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid('acheter' . $produit->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_produit_show', ['id' => $produit->getId()]);
        }

        if (!$user || !$user->isActif()) {
            $this->addFlash('danger', 'Votre compte est désactivé. Vous ne pouvez pas acheter de produit.');
            return $this->redirectToRoute('app_produit_show', ['id' => $produit->getId()]);
        }

        if ($user->getPoints() < $produit->getPrix()) {
            $this->addFlash('danger', 'Vous n’avez pas assez de points pour acheter ce produit.');
            return $this->redirectToRoute('app_produit_show', ['id' => $produit->getId()]);
        }

        // Effectuer l'achat
        $user->setPoints($user->getPoints() - $produit->getPrix());
        $entityManager->persist($user);

        // Créer une notification pour l’admin
        $notification = new Notification();
        $notification->setLabel($user->getPrenom() . ' ' . $user->getNom() . ' a acheté le produit "' . $produit->getNom() . '"');
        $notification->setUser($this->getAdminUser($entityManager));
        $entityManager->persist($notification);

        $entityManager->flush();

        $this->addFlash('success', 'Achat effectué avec succès !');
        return $this->redirectToRoute('app_produit_index');
    }

    private function getAdminUser(EntityManagerInterface $entityManager): ?\App\Entity\User
    {
        return $entityManager->getRepository(\App\Entity\User::class)
            ->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"ROLE_ADMIN"%')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
