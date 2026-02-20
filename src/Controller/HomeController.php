<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProductRepository;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository): Response
    {
        // Get the latest 4 products to show in the hero/bestsellers section.
        // If the DB is empty, an empty array will be passed and the template handles it.
        $bottles = $productRepository->findBy([], ['id' => 'DESC'], 4);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'Eco-bottle products',
            'bottles' => $bottles,
        ]);
    }
}
