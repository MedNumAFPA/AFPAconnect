<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', null, [
                'label' => 'Adresse email',
                'label_attr' => ['class' => 'required'],
                'attr' => [
                    'placeholder' => 'Entrez une adresse email valide...'
                ]
            ])
            // ->add('roles')
            ->add('plainpassword', PasswordType::class, [
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Entrez un mot de passe de 6 caractères minimum...'
                ]
            ])
        ;
        if ($options['allow_roles_update'] ?? false){
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

        ]);
    }
}
