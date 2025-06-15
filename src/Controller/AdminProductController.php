<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/produit')]
class AdminProductController extends AbstractController
{
    #[Route('/mine', name: 'admin_produit_mine', methods: ['GET'])]
    public function getMyProducts(ProduitRepository $produitRepository): JsonResponse
    {
        $user = $this->getUser();
        $produits = $produitRepository->findBy(['user' => $user]);

        $data = [];
        foreach ($produits as $produit) {
            $data[] = [
                'id' => $produit->getId(),
                'nom' => $produit->getNom(),
                'prix' => $produit->getPrix(),
                'category' => $produit->getCategory(),
                'description' => $produit->getDescription(),
                'createdAt' => $produit->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updatedAt' => $produit->getUpdatedAt()?->format('Y-m-d H:i:s'),
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', name: 'admin_produit_show_one', methods: ['GET'])]
    public function getOneProduct(Produit $produit): JsonResponse
    {
        $user = $this->getUser();
        if ($produit->getUser() !== $user) {
            return $this->json(['error' => 'Ce produit ne vous appartient pas.'], 403);
        }

        return $this->json([
            'id' => $produit->getId(),
            'nom' => $produit->getNom(),
            'prix' => $produit->getPrix(),
            'category' => $produit->getCategory(),
            'description' => $produit->getDescription(),
            'createdAt' => $produit->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updatedAt' => $produit->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ]);
    }
}
