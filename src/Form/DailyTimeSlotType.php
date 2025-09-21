<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DailyTimeSlotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('morningSlot', TimeSlotType::class, [
                'label' => 'Créneau du matin',
                'default_from_time' => new \DateTime('08:00'),
                'default_to_time' => new \DateTime('12:00'),
                'show_users' => false,
            ])
            ->add('hasAfternoonSlot', CheckboxType::class, [
                'label' => 'Ajouter un créneau l\'après-midi ?',
                'required' => false,
                'mapped' => false,
                'data' => true,
            ])
            ->add('afternoonSlot', TimeSlotType::class, [
                'label' => 'Créneau de l\'après-midi',
                'required' => false,
                'default_from_time' => new \DateTime('13:30'),
                'default_to_time' => new \DateTime('17:30'),
                'show_users' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
