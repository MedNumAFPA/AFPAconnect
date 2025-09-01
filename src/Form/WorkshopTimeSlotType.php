<?php

namespace App\Form;

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
            ])
            ->add('dailySlots', CollectionType::class, [
                'entry_type' => DailyTimeSlotType::class,
                'label' => 'Jours et créneaux',
                'allow_add' => true, // Permet d'ajouter de nouveaux éléments
                'allow_delete' => true, // Permet de supprimer des éléments
                'by_reference' => false,
                'prototype' => true, // Essentiel pour le JavaScript !
            ]);
    }
}
