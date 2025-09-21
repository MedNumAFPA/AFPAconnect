<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Workshop;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkshopType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'label_attr' => ['class' => 'required'],
                'attr' => [
                    'placeholder' => 'Entrez le nom de l\'atelier...'
                ]
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'label_attr' => ['class' => 'required'],
                'attr' => [
                    'placeholder' => 'Décrivez brièvement le contenu de l\'atelier...'
                ]
            ])
            ->add('maxSlots', IntegerType::class, [
                'label' => 'Places maximum',
                'label_attr' => ['class' => 'required'],
                'attr' => [
                    'placeholder' => 'Entrez le nombre de places disponibles maximum...'
                ]
            ])
            ->add('roomNumber', TextType::class, [
                'label' => 'N° de salle',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Entrez le n° de salle... (Optionnel, laissez vide pour entretien distanciel)'
                ]
            ])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nickname',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Selectionnez les animateurs en charge',
                'by_reference' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Workshop::class,
        ]);
    }
}
