<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Product>
 */
class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Produit')
            ->setEntityLabelInPlural('Produits');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->setLabel('ID')->onlyOnIndex(),
            TextField::new('designation')->setLabel('Designation'),
            TextareaField::new('description')->setLabel('Description')->hideOnIndex(),
            MoneyField::new('price')
                ->setLabel('Prix')
                ->setCurrency('EUR')
                ->setStoredAsCents(false),
            NumberField::new('stock')->setLabel('Stock'),
            AssociationField::new('category')->setLabel('Categorie'),
            TextField::new('capacity')->setLabel('Capacite'),
            TextField::new('temperature')->setLabel('Temperature'),
            ImageField::new('imgPath')
                ->setLabel('Image')
                ->setBasePath('products_images')
                // upload images in respective categories if category is set, otherwise upload to main products_images directory
                ->setUploadDir('public/products_images')
                ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
                ->setRequired(false),
        ];
    }
}
