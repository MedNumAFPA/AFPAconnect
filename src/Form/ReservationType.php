<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\TimeSlot;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
                'label_attr' => ['class' => 'required'],
                'attr' => [
                    'placeholder' => 'Entrez votre prénom...'
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom de famille',
                'required' => true,
                'label_attr' => ['class' => 'required'],
                'attr' => [
                    'placeholder' => 'Entrez votre nom de famille...'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'required' => true,
                'label_attr' => ['class' => 'required'],
                'attr' => [
                    'placeholder' => 'Entrez une adresse email valide...'
                ]
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Numéro de téléphone',
                'required' => true,
                'label_attr' => ['class' => 'required'],
                'attr' => [
                    'placeholder' => 'Entrez votre numéro de téléphone...'
                ]
            ])
            ->add('formationName', TextType::class, [
                'label' => 'Nom de la formation',
                'required' => false,
//                'label_attr' => ['class' => 'required'],
                'attr' => [
                    'placeholder' => 'Entrez votre nom de formation... (Optionnel, laissez vide si non applicable)'
                ]
            ])
            ->add('isReminderActive', CheckboxType::class, [
                'label' => 'Activer le rappel par email? (Vous recevrez un email 24h avant un atelier.)',
                'required' => false,
            ])
            ->add('timeslot', EntityType::class, [
                'class' => TimeSlot::class,
                'choice_label' => 'choiceLabel',
                'label' => 'Atelier et créneaux',
                'required' => true,
                'label_attr' => ['class' => 'required'],
                'attr' => [
                    'class' => 'form-combo'
                ],
                'placeholder' => 'Sélectionnez un créneau',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
