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
                // On passe les valeurs par défaut au sous-formulaire
                'default_from_time' => new \DateTime('08:00'),
                'default_to_time' => new \DateTime('12:00'),
            ])
            ->add('hasAfternoonSlot', CheckboxType::class, [
                'label' => 'Ajouter un créneau l\'après-midi ?',
                'required' => false,
                'mapped' => false, // Important : ce champ n'est pas lié à une entité
                'data' => true, // La case sera cochée par défaut
            ])
            ->add('afternoonSlot', TimeSlotType::class, [
                'label' => 'Créneau de l\'après-midi',
                'required' => false,
                // On passe les valeurs par défaut au sous-formulaire
                'default_from_time' => new \DateTime('13:30'),
                'default_to_time' => new \DateTime('17:30'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Ce formulaire n'est pas lié à une entité, c'est un simple conteneur
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}