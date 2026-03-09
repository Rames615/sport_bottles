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
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

/**
 * @extends AbstractCrudController<Promotion>
 */
class PromotionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Promotion::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Promotion')
            ->setEntityLabelInPlural('Promotions')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        // common fields for all pages
        $id = IdField::new('id')->onlyOnIndex();
        $title = TextField::new('title')->setLabel('Titre');
        $description = TextareaField::new('description')->setLabel('Description')->hideOnIndex();
        $discountType = ChoiceField::new('discountType')
            ->setLabel('Type de remise')
            ->setChoices([
                'Pourcentage' => Promotion::TYPE_PERCENTAGE,
                'Montant fixe' => Promotion::TYPE_FIXED,
            ]);
        $discountValue = NumberField::new('discountValue')->setLabel('Valeur de remise');
        $startAt = DateTimeField::new('startAt')->setLabel('Debut');
        $endAt = DateTimeField::new('endAt')->setLabel('Fin');
        $isActive = BooleanField::new('isActive')->setLabel('Active');
        $product = AssociationField::new('product')->setLabel('Produit');
        $image = ImageField::new('imgPath')
            ->setLabel('Image')
            ->setBasePath('products_images/promotion')
            ->setUploadDir('public/products_images/promotion')
            ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
            ->setRequired(false);
        $createdAt = DateTimeField::new('createdAt')->setLabel('Creee le')->onlyOnIndex();

        if (in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT])) {
            return [
                $title,
                $description,
                $image,
                $product,
                $discountType,
                $discountValue,
                $startAt,
                $endAt,
                $isActive,
            ];
        }

        return [$id, $title, $image, $product, $discountType, $discountValue, $startAt, $endAt, $isActive, $createdAt];
    }
}
