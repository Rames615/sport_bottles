<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Category.
 */
class CategoryTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $category = new Category();
        $category->setName('Isotherme');
        $category->setSlug('isotherme');
        $category->setDescription('Gourdes isothermes haute performance');

        $this->assertSame('Isotherme', $category->getName());
        $this->assertSame('isotherme', $category->getSlug());
        $this->assertSame('Gourdes isothermes haute performance', $category->getDescription());
    }

    public function testToString(): void
    {
        $category = new Category();
        $category->setName('Sport');

        $this->assertSame('Sport', (string) $category);
    }

    public function testAddAndRemoveProduct(): void
    {
        $category = new Category();
        $category->setName('Test');

        $product = new Product();
        $product->setDesignation('Gourde');
        $product->setDescription('Description');
        $product->setPrice('19.99');
        $product->setStock(5);
        $product->setCapacity('500ml');

        $category->addProduct($product);
        $this->assertCount(1, $category->getProducts());
        $this->assertSame($category, $product->getCategory());

        $category->removeProduct($product);
        $this->assertCount(0, $category->getProducts());
    }

    public function testSetUpdatedAt(): void
    {
        $category = new Category();
        $date = new \DateTime('2026-03-15');
        $category->setUpdatedAt($date);

        $this->assertSame($date, $category->getUpdatedAt());
    }

    public function testProductsCollectionIsEmptyByDefault(): void
    {
        $category = new Category();
        $this->assertCount(0, $category->getProducts());
    }
}
