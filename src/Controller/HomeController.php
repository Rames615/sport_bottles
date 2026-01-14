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
                'imgUrl' => '/images_products/acier_inoxydable.png'
            ],
            [
                'name' => 'isothermique',
                'size' => '750ml',
                'color' => 'Blue',
                'material' => 'Aluminum',
                'price' => '14.50',
                'description' => 'Lightweight aluminum bottle perfect for sports enthusiasts.',
                'imgUrl' => '/images_products/isothermique_blue.png'
            ],
            [
                'name' => 'Verre',
                'size' => '350ml',
                'color' => 'Vert',
                'material' => 'Glass',
                'price' => '22.00',
                'description' => 'Made of reinforced glass, ideal for preserving natural taste.',
                'imgUrl' => '/images_products/glass_green.png'
            ],
            [
                'name' => 'Plastique BPA-Free',
                'size' => '350ml',
                'color' => 'blue',
                'material' => 'Plastic (BPA-Free)',
                'price' => '8.90',
                'description' => 'Compact and kid-friendly bottle made from BPA-free plastic.',
                'imgUrl' => '/images_products/sky_blue_sans_bpa.png'
            ],
            [
                'name' => 'ThermoPro',
                'size' => '1L',
                'color' => 'Black',
                'material' => 'Stainless Steel',
                'price' => '29.99',
                'description' => 'Premium insulated bottle maintaining temperature for up to 24 hours.',
                'imgUrl' => 'https://www.yokodesign.fr/1343-large_default/bouteilles-isothermes-xl-grandes.jpg'
            ],
            [
                'name' => 'OutdoorMax',
                'size' => '900ml',
                'color' => 'Green',
                'material' => 'Aluminum',
                'price' => '17.49',
                'description' => 'Ideal for outdoor activities, featuring improved durability.',
                'imgUrl' => 'https://www.neoflam.com.au/cdn/shop/products/Studio-Session4-509-1000px.jpg?v=1643005490'
            ],
            [
                'name' => 'CrystalLite',
                'size' => '500ml',
                'color' => 'White',
                'material' => 'Glass',
                'price' => '18.75',
                'description' => 'Elegant glass bottle with silicone protection sleeve.',
                'imgUrl' => 'https://shop.bestsublimation24.eu/hpeciai/da0988688d76f3f3d54d79eea520adad/eng_pl_750-ml-water-bottle-with-a-sublimation-flap-white-8066_4.jpg'
            ],
            [
                'name' => 'UrbanFlow',
                'size' => '600ml',
                'color' => 'Grey',
                'material' => 'Plastic (BPA-Free)',
                'price' => '11.30',
                'description' => 'Modern BPA-free bottle suitable for daily urban use.',
                'imgUrl' => 'https://www.ion8.co.uk/cdn/shop/files/B0BS6VDJSG.MAIN_c1055ceb-17fe-4659-983f-1aeec416bbf7.jpg?v=1737026515&width=1946'
            ],
            ],
        ]);
    }
}
