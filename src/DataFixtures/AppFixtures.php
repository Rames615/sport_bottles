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
            // verres
            ['designation'=>'Bouteille verre','description'=>'Bouteille en verre soufflé, élégante et résistante.','price'=>'12.90','imgPath'=>'verre_blue.png','capacity'=>'500ml','temperature'=>null,'category'=>'verres'],
            ['designation'=>'Bouteille verre','description'=>'Verre teinté vert, parfait pour usage quotidien.','price'=>'11.50','imgPath'=>'glass_green.png','capacity'=>'750ml','temperature'=>null,'category'=>'verres'],
            ['designation'=>'Bouteille verre','description'=>'Design épuré, idéale pour une utilisation quotidienne.','price'=>'14.20','imgPath'=>'glass_degrade_blue.png','capacity'=>'1L','temperature'=>null,'category'=>'verres'],

            // inox
            ['designation'=>'Bouteille inox','description'=>'Acier inoxydable durable, finition verte.','price'=>'24.99','imgPath'=>'acier_inoxydable_vert.png','capacity'=>'1L','temperature'=>null,'category'=>'inox'],
            ['designation'=>'Bouteille Acier Inoxydable','description'=>'Matériau acier, léger et pratique.','price'=>'10.99','imgPath'=>'acier_inoxydable.png','capacity'=>'500ml','temperature'=>null,'category'=>'inox'],
            ['designation'=>'Bouteille inoxydable','description'=>'Finition charcoal, robuste et élégante.','price'=>'19.50','imgPath'=>'acier_inox_charcoal.png','capacity'=>'750ml','temperature'=>null,'category'=>'inox'],

            // isothermiques
            ['designation'=>'Bouteille isothermique','description'=>'Garde boissons chaudes/froides pendant des heures.','price'=>'29.90','imgPath'=>'isothermique_blue.png','capacity'=>'500ml','temperature'=>'12h','category'=>'isothermiques'],
            ['designation'=>'Bouteille isothermique','description'=>'Double paroi pour une isolation optimale.','price'=>'34.50','imgPath'=>'isothermique_red.png','capacity'=>'750ml','temperature'=>'24h','category'=>'isothermiques'],
            ['designation'=>'Bouteille isothermique','description'=>'Parfaite pour les boissons chaudes et froides.','price'=>'27.80','imgPath'=>'isothermique_green.png','capacity'=>'1L','temperature'=>'18h','category'=>'isothermiques'],

            // sans-bpa
            ['designation'=>'Bouteille sans BPA','description'=>'Matériau sans BPA, léger et pratique.','price'=>'9.90','imgPath'=>'transparent_sans_bpa.png','capacity'=>'750ml','temperature'=>null,'category'=>'sans-bpa'],
            ['designation'=>'Bouteille sans BPA','description'=>'Design épuré, idéale pour une utilisation quotidienne.','price'=>'8.50','imgPath'=>'sky_blue_sans_bpa.png','capacity'=>'500ml','temperature'=>null,'category'=>'sans-bpa'],
            ['designation'=>'Bouteille sans BPA','description'=>'Couleur dégradée, moderne et tendance.','price'=>'11.20','imgPath'=>'degrade_pink_sans_bpa.png','capacity'=>'1L','temperature'=>null,'category'=>'sans-bpa'],
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
