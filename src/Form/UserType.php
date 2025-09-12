<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nickname', null, [
                'label' => 'Pseudo',
                'label_attr' => ['class' => 'required'],
                'attr' => [
                    'placeholder' => 'Entrez un pseudo valide...'
                ]
            ])
            ->add('email', null, [
                'label' => 'Adresse email',
                'label_attr' => ['class' => 'required'],
                'attr' => [
                    'placeholder' => 'Entrez une adresse email valide...'
                ]
            ]);
        if ($options['is_add']) {
            $builder->add('plainpassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'label_attr' => ['class' => 'required'],
                    'attr' => [
                        'placeholder' => 'Entrez un mot de passe valide...'
                    ]
                ],
                'second_options' => [
                    'label' => 'Répètez mot de passe',
                    'label_attr' => ['class' => 'required'],
                    'attr' => [
                        'placeholder' => 'Répètez votre mot de passe...'
                    ]
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'mapped' => false,
                'required' => true,
            ]);
        }
        if ($options['allow_roles_update'] ?? false) {
            $builder->add('roles', ChoiceType::class, [
                'label' => 'Rôles Utilisateur',
                'choices' => [
                    'Administrateur' => 'ROLE_ADMIN',
                    'Editeur' => 'ROLE_EDITOR',
                    'Utilisateur' => 'ROLE_USER',
                ],
                'multiple' => true,
                'expanded' => true,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'allow_roles_update' => false, // Par défaut, l'édition des rôles est désactivée
            'is_add' => false,  // valeur par défaut
        ]);
    }
}
