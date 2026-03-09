<?php

namespace App\Controller\Admin;

use App\Entity\Cart;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

/**
 * @extends AbstractCrudController<Cart>
 */
class CartCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Cart::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Panier')
            ->setEntityLabelInPlural('Paniers');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->setLabel('ID')->onlyOnIndex(),
            AssociationField::new('user')->setLabel('Client'),
            AssociationField::new('items')->setLabel('Articles')->onlyOnDetail(),
            DateTimeField::new('createdAt')->setLabel('Cree le')->hideOnForm(),
            DateTimeField::new('updatedAt')->setLabel('Mis a jour le')->hideOnForm(),
        ];
    }
}
