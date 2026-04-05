<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/** @extends AbstractType<array<string, mixed>> */
class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Votre nom',
                'attr'  => ['placeholder' => 'Jean Dupont'],
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez entrer votre nom.'),
                    new Assert\Length(min: 2, max: 100),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'attr'  => [
                    'placeholder'    => 'prenom@exemple.com',
                    'autocomplete'   => 'email',
                    'inputmode'      => 'email',
                    'spellcheck'     => 'false',
                    'autocapitalize' => 'none',
                    'autocorrect'    => 'off',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez entrer votre adresse e-mail.'),
                    new Assert\Email(mode: 'html5', message: 'Veuillez entrer une adresse e-mail valide (ex : prenom@exemple.com).'),
                ],
            ])
            ->add('subject', TextType::class, [
                'label'    => 'Sujet',
                'required' => false,
                'attr'     => ['placeholder' => 'Objet de votre message'],
                'constraints' => [
                    new Assert\Length(max: 200),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr'  => ['placeholder' => 'Votre message...', 'rows' => 6],
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez écrire un message.'),
                    new Assert\Length(
                        min: 10,
                        max: 2000,
                        minMessage: 'Votre message doit contenir au moins {{ limit }} caractères.',
                        maxMessage: 'Votre message ne peut pas dépasser {{ limit }} caractères.'
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'contact_form',
        ]);
    }
}