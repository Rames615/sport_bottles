<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'Eco-bottle products',
            'bottles' => $bottles = [
                [
                'name' => 'Acier inoxydable',
                'size' => '500ml',
                'color' => 'white',
                'material' => 'Stainless Steel',
                'price' => '19.99',
                'description' => 'A durable stainless steel bottle designed to keep drinks cold for up to 12 hours.',
                'imgUrl' => '/products_images/inox/acier_inoxydable.png'
            ],
            [
                'name' => 'isothermique',
                'size' => '750ml',
                'color' => 'Blue',
                'material' => 'Aluminum',
                'price' => '14.50',
                'description' => 'Lightweight aluminum bottle perfect for sports enthusiasts.',
                'imgUrl' => '/products_images/isothermiques/isothermique_blue.png'
            ],
            [
                'name' => 'Verre',
                'size' => '350ml',
                'color' => 'Vert',
                'material' => 'Glass',
                'price' => '22.00',
                'description' => 'Made of reinforced glass, ideal for preserving natural taste.',
                'imgUrl' => '/products_images/verres/glass_green.png'
            ],
            [
                'name' => 'Plastique BPA-Free',
                'size' => '350ml',
                'color' => 'blue',
                'material' => 'Plastic (BPA-Free)',
                'price' => '8.90',
                'description' => 'Compact and kid-friendly bottle made from BPA-free plastic.',
                'imgUrl' => '/products_images/sans-bpa/sky_blue_sans_bpa.png'
            ],
            
            ],
        ]);
    }
}
