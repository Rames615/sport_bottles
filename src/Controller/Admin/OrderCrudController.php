<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

/**
 * @extends AbstractCrudController<Order>
 */
class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commande')
            ->setEntityLabelInPlural('Commandes')
            ->setDefaultSort(['createdAt' => 'DESC']);
            
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('reference')->setLabel('Reference')->hideOnForm(),
            AssociationField::new('user')->setLabel('Client'),
            TextField::new('shippingAddress')->setLabel('Adresse de livraison'),
            MoneyField::new('totalAmount')->setLabel('Montant total')->setCurrency('EUR')->setStoredAsCents(true),
            ChoiceField::new('status')->setChoices([
                'En attente' => 'pending',
                'Payé' => 'paid',
                'Annulé' => 'cancelled',
            ])->setLabel('Statut'),
            TextField::new('stripeSessionId')->setLabel('Session Stripe')->onlyOnDetail(),
            DateTimeField::new('createdAt')->setLabel('Créé le'),
            // ajouter le email du client dans la liste des commandes
            TextField::new('user.email')->setLabel('Email du client')->onlyOnIndex()
        ];
    }
}
