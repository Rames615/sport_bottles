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
            ['designation'=>' verre','description'=>'Bouteille en verre soufflé, élégante et résistante.','price'=>'12.90','imgPath'=>'verre_blue.png','capacity'=>'500ml','temperature'=>null,'category'=>'verres'],
            ['designation'=>' verre','description'=>'Verre teinté vert, parfait pour usage quotidien.','price'=>'11.50','imgPath'=>'glass_green.png','capacity'=>'750ml','temperature'=>null,'category'=>'verres'],
            ['designation'=>' verre','description'=>'Design épuré, idéale pour une utilisation quotidienne.','price'=>'14.20','imgPath'=>'glass_degrade_blue.png','capacity'=>'1L','temperature'=>null,'category'=>'verres'],
            ['designation'=>' verre','description'=>'Bouteille en verre avec la bonne protection sans avoir le risque de endommagement, classique et élégante.','price'=>'13.50','imgPath'=>'rusty.png','capacity'=>'500ml','temperature'=>null,'category'=>'verres'],
            // ino
            ['designation'=>' inox','description'=>'Acier inoxydable durable, finition verte.','price'=>'24.99','imgPath'=>'acier_inoxydable_vert.png','capacity'=>'1L','temperature'=>null,'category'=>'inox'],
            ['designation'=>' Acier Inoxydable','description'=>'Matériau acier, léger et pratique.','price'=>'10.99','imgPath'=>'acier_inoxydable.png','capacity'=>'500ml','temperature'=>null,'category'=>'inox'],
            ['designation'=>' inoxydable','description'=>'Finition charcoal, robuste et élégante.','price'=>'19.50','imgPath'=>'acier_inox_charcoal.png','capacity'=>'750ml','temperature'=>null,'category'=>'inox'],
            ['designation'=>' inoxydable','description'=>'Finition chrome, robuste et élégante, facile à porter.','price'=>'10.50','imgPath'=>'rusty-acier.png','capacity'=>'750ml','temperature'=>null,'category'=>'inox'],
            // isothermique
            ['designation'=>' isothermique','description'=>'Garde boissons chaudes/froides pendant des heures.','price'=>'29.90','imgPath'=>'isothermique_blue.png','capacity'=>'500ml','temperature'=>'12h','category'=>'isothermiques'],
            ['designation'=>' isothermique','description'=>'Double paroi pour une isolation optimale.','price'=>'34.50','imgPath'=>'isothermique_red.png','capacity'=>'750ml','temperature'=>'24h','category'=>'isothermiques'],
            ['designation'=>' isothermique','description'=>'Parfaite pour les boissons chaudes et froides.','price'=>'27.80','imgPath'=>'isothermique_green.png','capacity'=>'1L','temperature'=>'18h','category'=>'isothermiques'],
            ['designation'=>' isothermique','description'=>'Parfaite pour les boissons chaudes et froides et élégante.','price'=>'15.80','imgPath'=>'rusty-termos.png','capacity'=>'1L','temperature'=>'18h','category'=>'isothermiques'],
            // sans-bp
            ['designation'=>' sans BPA','description'=>'Matériau sans BPA, léger et pratique.','price'=>'9.90','imgPath'=>'transparent_sans_bpa.png','capacity'=>'750ml','temperature'=>null,'category'=>'sans-bpa'],
            ['designation'=>' sans BPA','description'=>'Design épuré, idéale pour une utilisation quotidienne.','price'=>'8.50','imgPath'=>'sky_blue_sans_bpa.png','capacity'=>'500ml','temperature'=>null,'category'=>'sans-bpa'],
            ['designation'=>' sans BPA','description'=>'Couleur dégradée, moderne et tendance.','price'=>'11.20','imgPath'=>'degrade_pink_sans_bpa.png','capacity'=>'1L','temperature'=>null,'category'=>'sans-bpa'],
            ['designation'=>' sans BPA','description'=>'Couleur rose, moderne et tendance.','price'=>'14.20','imgPath'=>'bpa-free-rose.png','capacity'=>'1L','temperature'=>null,'category'=>'sans-bpa'],
        ];

        $products = [];
        $i = 0;
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

            // keep reference for promotions
            $products[] = $p;
            $this->addReference('product_' . $i, $p);
            $i++;
        }

        $manager->flush();

        // create a few active promotions for the front page
        $promoData = [
            ['ref' => 'product_0', 'type' => \App\Entity\Promotion::TYPE_PERCENTAGE, 'value' => 20, 'title' => '20% sur bouteille verre', 'description' => 'Offre spéciale sur notre bouteille en verre.', 'durationDays' => 10, 'imgPath'=>'bpa_free_transparent.jpeg'],
            ['ref' => 'product_3', 'type' => \App\Entity\Promotion::TYPE_FIXED, 'value' => 5, 'title' => '5€ de réduction inox', 'description' => 'Économisez 5€ sur la bouteille inox.', 'durationDays' => 14, 'imgPath'=>'glass_forest_green.png'],
            ['ref' => 'product_6', 'type' => \App\Entity\Promotion::TYPE_PERCENTAGE, 'value' => 15, 'title' => 'Promotion isothermique', 'description' => '-15% sur modèle isothermique', 'durationDays' => 7, 'imgPath'=>'inox_gray.png'],
            ['ref' => 'product_9', 'type' => \App\Entity\Promotion::TYPE_FIXED, 'value' => 3, 'title' => 'Réduction Sans BPA', 'description' => '3€ offerts sur les bouteilles sans BPA.', 'durationDays' => 12, 'imgPath'=>'isothermic_forest_green.png'],
            ['ref' => 'product_1', 'type' => \App\Entity\Promotion::TYPE_PERCENTAGE, 'value' => 10, 'title' => '10% sur verre teinté', 'description' => 'Profitez de 10% de réduction sur notre bouteille en verre teinté.', 'durationDays' => 5, 'imgPath'=>'verre.png'],
            ['ref' => 'product_4', 'type' => \App\Entity\Promotion::TYPE_FIXED, 'value' => 7, 'title' => '7€ de réduction inox', 'description' => 'Offre exceptionnelle : 7€ de réduction sur notre bouteille en acier inoxydable.', 'durationDays' => 20, 'imgPath'=>'isothermique_red.png'],
        ];

        foreach ($promoData as $pd) {
            $promo = new \App\Entity\Promotion();
            // retrieve product reference (class required by current Doctrine version)
            /** @var \App\Entity\Product $product */
            $product = $this->getReference($pd['ref'], \App\Entity\Product::class);
            $promo->setProduct($product);
            $promo->setDiscountType($pd['type']);
            $promo->setDiscountValue($pd['value']);
            $promo->setTitle($pd['title']);
            $promo->setDescription($pd['description']);
            $promo->setImgPath($pd['imgPath']);
            $promo->setIsActive(true);
            $start = new \DateTimeImmutable();
            $promo->setStartAt($start);
            $promo->setEndAt($start->modify('+' . $pd['durationDays'] . ' days'));
            $manager->persist($promo);
        }

        $manager->flush();
    }
    // UserFactory::new()
    // ->withAttributes([
    //     'email' => 'admin@example.com',
    //     'password' => 'adminpass',
    // ])
    // ->promoteRole('ROLE_SUPER_ADMIN')
    // ->create();
}
