<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Workshop; // Assurez-vous d'utiliser le nom au singulier si vous avez renommé
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class WorkshopTimeSlotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('workshop', EntityType::class, [
                'class' => Workshop::class,
                'choice_label' => 'name',
                'label' => 'Sélectionnez un atelier',
                'label_attr' => ['class' => 'required'],
                'attr' => [
                    'class' => 'w-full p-2 border border-gray-300 rounded-md bg-gray-50 focus:ring-primary focus:border-primary'
                ],
                'placeholder' => 'Choisissez dans la liste...'
            ])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nickname',
                'label' => 'Animateur(s) assigné(s)',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'mapped' => false,
                'label_attr' => ['class' => 'required'],
//                'attr' => [
//                    'class' => 'hidden',
//                ]
                'row_attr' => [
                    'class' => 'workshop-users-field hidden',
                ]
            ])
            ->add('dailySlots', CollectionType::class, [
                'entry_type' => DailyTimeSlotType::class,
                'label' => 'Jours et créneaux',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true, // Essential for JS
            ]);
    }
}
