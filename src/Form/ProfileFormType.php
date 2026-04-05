<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/** @extends AbstractType<User> */
class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'attr' => [
                    'placeholder'    => 'prenom@exemple.com',
                    'autocomplete'   => 'email',
                    'inputmode'      => 'email',
                    'spellcheck'     => 'false',
                    'autocapitalize' => 'none',
                    'autocorrect'    => 'off',
                ],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre adresse e-mail.'),
                    new Email(mode: 'html5', message: 'Veuillez entrer une adresse e-mail valide (ex : prenom@exemple.com).'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
