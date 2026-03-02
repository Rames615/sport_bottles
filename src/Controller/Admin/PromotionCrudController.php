<?php

namespace App\Controller\Admin;

use App\Entity\Promotion;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

/**
 * @extends AbstractCrudController<Promotion>
 */
class PromotionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Promotion::class;
    }

    public function configureFields(string $pageName): iterable
    {
        // common fields for all pages
        $id = IdField::new('id')->onlyOnIndex();
        $title = TextField::new('title');
        $description = TextareaField::new('description')->hideOnIndex();
        $discountType = ChoiceField::new('discountType')
            ->setChoices([
                'Pourcentage' => Promotion::TYPE_PERCENTAGE,
                'Montant fixe' => Promotion::TYPE_FIXED,
            ]);
        $discountValue = NumberField::new('discountValue');
        $startAt = DateTimeField::new('startAt');
        $endAt = DateTimeField::new('endAt');
        $isActive = BooleanField::new('isActive');
        $product = AssociationField::new('product');
        $createdAt = DateTimeField::new('createdAt')->onlyOnIndex();

        if (in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT])) {
            return [
                $title,
                $description,
                $product,
                $discountType,
                $discountValue,
                $startAt,
                $endAt,
                $isActive,
            ];
        }

        return [$id, $title, $product, $discountType, $discountValue, $startAt, $endAt, $isActive, $createdAt];
    }
}
