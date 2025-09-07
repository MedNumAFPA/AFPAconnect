<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\TimeSlot;
use App\Repository\TimeSlotRepository;
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
        // These fields are always present
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
                'label_attr' => ['class' => 'required'],
                'attr' => ['placeholder' => 'Entrez votre prénom...']
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom de famille',
                'required' => true,
                'label_attr' => ['class' => 'required'],
                'attr' => ['placeholder' => 'Entrez votre nom de famille...']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'required' => true,
                'label_attr' => ['class' => 'required'],
                'attr' => ['placeholder' => 'Entrez une adresse email valide...']
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Numéro de téléphone',
                'required' => true,
                'label_attr' => ['class' => 'required'],
                'attr' => ['placeholder' => 'Entrez votre numéro de téléphone...']
            ])
            ->add('idAfpa', TextType::class, [
                'label' => 'Identifiant AFPA',
                'required' => true,
                'label_attr' => ['class' => 'required'],
                'attr' => ['placeholder' => 'Entrez votre numero de formation...']
            ])
            ->add('formationName', TextType::class, [
                'label' => 'Nom de la formation',
                'required' => false,
                'attr' => ['placeholder' => 'Entrez votre nom de formation...']
            ])
            ->add('isReminderActive', CheckboxType::class, [
                'label' => 'Activer le rappel par email?',
                'required' => false,
            ]);

        // --- THIS IS THE DEFINITIVE FIX ---

        // We only add the 'timeslot' field if we are NOT in workshop subscription mode.
        if (!$options['is_workshop_subscription']) {


            $timeslotOptions = [
                'class' => TimeSlot::class, // <-- THE FIX
                'choice_label' => 'choiceLabel',
                'label' => 'Atelier et créneaux',
                'required' => true,
                'label_attr' => ['class' => 'required'],
                'attr' => ['class' => 'form-combo'],
                'placeholder' => 'Sélectionnez un créneau',
            ];

            if ($options['workshop_id']) {
                $workshopId = $options['workshop_id'];
                $timeslotOptions['query_builder'] = function (TimeSlotRepository $tsr) use ($workshopId) {
                    return $tsr->createQueryBuilder('ts')
                        ->where('ts.workshop = :workshopId')
                        ->setParameter('workshopId', $workshopId)
                        ->orderBy('ts.date', 'ASC')
                        ->addOrderBy('ts.fromTime', 'ASC');
                };
            }

            // 3. Add the field to the builder ONCE with the final, complete set of options.
            $builder->add('timeslot', EntityType::class, $timeslotOptions);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
            'is_workshop_subscription' => false,
            'workshop_id' => null,
        ]);
    }
}
