<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $now = new \DateTime();

        // Create sample categories
        $categoriesData = [
            ['name' => 'Verres', 'slug' => 'verres', 'description' => 'Bouteilles et contenants en verre'],
            ['name' => 'Inoxydable', 'slug' => 'inox', 'description' => 'Acier inoxydable durable'],
            ['name' => 'Isothermiques', 'slug' => 'isothermiques', 'description' => 'Garde la température longtemps'],
            ['name' => 'Sans BPA', 'slug' => 'sans-bpa', 'description' => 'Matériaux sans BPA sûrs'],
        ];

        $categories = [];
        foreach ($categoriesData as $cd) {
            $c = new \App\Entity\Category();
            $c->setName($cd['name']);
            $c->setSlug($cd['slug']);
            $c->setDescription($cd['description']);
            $c->setUpdatedAt($now);
            $manager->persist($c);
            $categories[$cd['slug']] = $c;
        }

        // Create sample products
        $productsData = [
            ['designation'=>'Bouteille verre bleue','description'=>'Bouteille en verre soufflé, élégante et résistante.','price'=>'12.90','imgPath'=>'verre_blue.png','capacity'=>'500ml','temperature'=>null,'category'=>'verres'],
            ['designation'=>'Bouteille verre verte','description'=>'Verre teinté vert, parfait pour usage quotidien.','price'=>'11.50','imgPath'=>'glass_green.png','capacity'=>'750ml','temperature'=>null,'category'=>'verres'],
            ['designation'=>'Bouteille inox vert','description'=>'Acier inoxydable durable, finition verte.','price'=>'24.99','imgPath'=>'acier_inoxydable_vert.png','capacity'=>'1L','temperature'=>null,'category'=>'inox'],
            ['designation'=>'Bouteille isothermique bleue','description'=>'Garde boissons chaudes/froides pendant des heures.','price'=>'29.90','imgPath'=>'isothermique_blue.png','capacity'=>'500ml','temperature'=>'12h','category'=>'isothermiques'],
            ['designation'=>'Bouteille sans BPA transparente','description'=>'Matériau sans BPA, léger et pratique.','price'=>'9.90','imgPath'=>'transparent_sans_bpa.png','capacity'=>'750ml','temperature'=>null,'category'=>'sans-bpa'],
        ];

        foreach ($productsData as $pd) {
            $p = new \App\Entity\Product();
            $p->setDesignation($pd['designation']);
            $p->setDescription($pd['description']);
            $p->setPrice($pd['price']);
            $p->setImgPath($pd['imgPath']);
            $p->setCapacity($pd['capacity']);
            $p->setTemperature($pd['temperature']);
            $p->setCategory($categories[$pd['category']]);
            $p->setStock(100); // Set stock to 100 for all products
            $manager->persist($p);
        }

        $manager->flush();
    }
}
