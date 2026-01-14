<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository
    ): Response {
        $categories = $categoryRepository->findAll();
        $products = $productRepository->findAll();

        // Initialize all categories as empty
        $productsByCategory = [];

        foreach ($categories as $category) {
            $productsByCategory[$category->getSlug()] = [
                'category' => $category,
                'products' => [],
            ];
        }

        // Assign products to their category
        foreach ($products as $product) {
            $category = $product->getCategory();

            if ($category) {
                $productsByCategory[$category->getSlug()]['products'][] = $product;
            }
        }

        return $this->render('product/index.html.twig', [
            'categories' => $categories,
            'productsByCategory' => $productsByCategory,
            'allProducts' => $products,
        ]);
    }
}
