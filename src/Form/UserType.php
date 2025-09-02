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
            ->add('nickname')
            ->add('email', null, [
                'label' => 'Adresse email',
                'label_attr' => ['class' => 'required'],
                'attr' => [
                'placeholder' => 'Entrez une adresse email valide...'
                ]
            ])
            // ->add('roles')
            ->add('plainpassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Ajouter nouveau mot de passe'],
                'second_options' => ['label' => 'Répétez le nouveau mot de passe'],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'mapped' => false,
                'required' => true,
            ])
            
        ;
        if ($options['is_edit']) {
            $builder->add('oldPassword', PasswordType::class, [
                'label' => 'Ancien mot de passe',
                'mapped' => false,
                'constraints' => [
                    new UserPassword(['message' => 'Ancien mots de passe est incorect.']),
                ],
            ]);
        }
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
            'is_edit' => false,  // valeur par défaut
        ]);
    }
}
