<?php

namespace App\Form;

use App\Entity\ShippingAddress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/** @extends AbstractType<ShippingAddress> */
class ShippingAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'Nom complet',
                'attr' => [
                    'placeholder' => 'ex: Jean Dupont',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(message: 'Le nom complet est obligatoire.'),
                    new Length(
                        min: 3,
                        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.',
                        max: 255,
                        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.',
                    ),
                ],
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'attr' => [
                    'placeholder' => 'ex: 123 Rue de la Paix',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(message: 'L\'adresse est obligatoire.'),
                    new Length(
                        min: 5,
                        minMessage: 'L\'adresse doit contenir au moins {{ limit }} caractères.',
                        max: 255,
                        maxMessage: 'L\'adresse ne peut pas dépasser {{ limit }} caractères.',
                    ),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'attr' => [
                    'placeholder' => 'ex: Paris',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(message: 'La ville est obligatoire.'),
                    new Length(
                        min: 2,
                        minMessage: 'La ville doit contenir au moins {{ limit }} caractères.',
                        max: 100,
                        maxMessage: 'La ville ne peut pas dépasser {{ limit }} caractères.',
                    ),
                ],
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'attr' => [
                    'placeholder' => 'ex: 75001',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(message: 'Le code postal est obligatoire.'),
                    new Regex(
                        pattern: '/^\d{5}$/',
                        message: 'Le code postal doit contenir 5 chiffres.',
                    ),
                ],
            ])
            ->add('country', TextType::class, [
                'label' => 'Pays',
                'attr' => [
                    'placeholder' => 'ex: France',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(message: 'Le pays est obligatoire.'),
                    new Length(
                        min: 2,
                        minMessage: 'Le pays doit contenir au moins {{ limit }} caractères.',
                        max: 100,
                        maxMessage: 'Le pays ne peut pas dépasser {{ limit }} caractères.',
                    ),
                ],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'placeholder' => 'ex: +33 6 12 34 56 78',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(message: 'Le téléphone est obligatoire.'),
                    new Regex(
                        pattern: '/^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/',
                        message: 'Le numéro de téléphone n\'est pas valide.',
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ShippingAddress::class,
        ]);
    }
}
