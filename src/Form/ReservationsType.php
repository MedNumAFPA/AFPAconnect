<?php

namespace App\Form;

use App\Entity\Reservations;
use App\Entity\TimeSlots;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom' // <-- Traduction
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom de famille' // <-- Traduction
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email' // <-- Traduction
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Numéro de téléphone' // <-- Traduction
            ])
            ->add('formationName', TextType::class, [
                'label' => 'Nom de la formation' // <-- Traduction
            ])
            ->add('isReminderActive', CheckboxType::class, [
                'label' => 'Activer le rappel par email? (Vous recevrez un email 24h avant un atelier.)', // <-- Traduction
                'required' => false,
            ])
            ->add('timeslot', EntityType::class, [
                'class' => TimeSlots::class,
                'choice_label' => 'id',
                'attr' => ['class' => 'form-combo'],
                'label' => 'Créneau'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservations::class,
        ]);
    }
}
