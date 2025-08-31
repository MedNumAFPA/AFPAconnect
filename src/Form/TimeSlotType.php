<?php

namespace App\Form;

use App\Entity\TimeSlot;
use App\Entity\Workshop;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimeSlotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text', // Pour un meilleur affichage
            ])
            ->add('fromTime', TimeType::class, [
                'label' => 'Heure de début',
                'widget' => 'single_text',
            ])
            ->add('toTime', TimeType::class, [
                'label' => 'Heure de fin',
                'widget' => 'single_text',
            ])
            ->add('workshop', EntityType::class, [
                'class' => Workshop::class,
                'choice_label' => 'name',
                'label' => 'Atelier',
                'attr' => ['class' => 'form-combo']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TimeSlot::class,
        ]);
    }
}
