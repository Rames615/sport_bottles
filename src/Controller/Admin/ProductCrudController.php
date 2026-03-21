<?php

namespace App\Controller\Admin;

use App\Entity\Product;
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
            TextField::new('designation')->setLabel('Désignation'),
            TextField::new('shortDescription')->setLabel('Description courte')->setHelp('Résumé affiché sur les cartes produit (max 160 car.). Si vide, la description longue sera tronquée.')->setRequired(false),
            TextareaField::new('description')->setLabel('Description complète')->hideOnIndex(),
            MoneyField::new('price')
                ->setLabel('Prix')
                ->setCurrency('EUR')
                ->setStoredAsCents(false),
            NumberField::new('stock')->setLabel('Stock'),
            AssociationField::new('category')->setLabel('Catégorie'),
            TextField::new('capacity')->setLabel('Capacité'),
            TextField::new('temperature')->setLabel('Température'),
            ImageField::new('imgPath')
                ->setLabel('Image')
                ->setBasePath('products_images')
                ->setUploadDir('public/products_images')
                ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
                ->setRequired(false),
        ];
    }
}
