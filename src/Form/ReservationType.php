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
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
                'label_attr' => ['class' => 'required'],
                'attr' => ['placeholder' => 'Entrez votre prénom...']
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
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
                'label' => 'Identifiant AFPA (de votre carte stagiaire)',
                'required' => true,
                'label_attr' => ['class' => 'required'],
                'attr' => ['placeholder' => 'Entrez votre numero de formation...']
            ])
            ->add('formationName', TextType::class, [
                'label' => 'Nom de la formation suivi',
                'required' => false,
                'attr' => ['placeholder' => 'Entrez votre nom de formation...']
            ]);

        if (!$options['is_workshop_subscription']) {
            $timeslotOptions = [
                'class' => TimeSlot::class,
                'choice_label' => function (TimeSlot $slot) {
                    return sprintf(
                        '%s (%s - %s)',
                        $slot->getDate()->format('d/m/Y'),
                        $slot->getFromTime()->format('H:i'),
                        $slot->getToTime()->format('H:i')
                    );
                },
                'label' => 'Créneaux disponibles',
                'required' => true,
                'placeholder' => 'Sélectionnez un créneau',
            ];

            if ($options['workshop_id']) {
                $workshopId = $options['workshop_id'];
                $timeslotOptions['query_builder'] = static function (TimeSlotRepository $tsr) use ($workshopId) {
                    return $tsr->createQueryBuilder('ts')
                        ->where('ts.workshop = :workshopId')
                        ->andWhere('ts.reservations IS EMPTY')
                        ->setParameter('workshopId', $workshopId)
                        ->orderBy('ts.date', 'ASC')
                        ->addOrderBy('ts.fromTime', 'ASC');
                };
            }

            $builder->add('timeslot', EntityType::class, $timeslotOptions);
        }


//        TODO: add check for slots, render slots per timeslots on homepage, etc ...

        if ($options['is_workshop_subscription']) {
            $builder->add('timeslots', EntityType::class, [
                'class' => TimeSlot::class,
                'choice_label' => function (TimeSlot $slot) {
                    $reservationCount = count($slot->getReservations());
                    $maxSlots = $slot->getWorkshop()->getMaxSlots();
                    return sprintf(
                        '<div class="flex flex-col">
                                    <span class="block">%s</span>
                                    <span class="block">(%s - %s)</span>
                                    <span class="block">%d/%d Places réservées</span>
                                </div>',
                        $slot->getDate()->format('d/m/Y'),
                        $slot->getFromTime()->format('H:i'),
                        $slot->getToTime()->format('H:i'),
                        $reservationCount,
                        $maxSlots
                    );
                },
                'label_html' => true,
                'label' => 'Sélectionnez vos créneaux',
                'required' => true,
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
                'query_builder' => function (TimeSlotRepository $tsr) use ($options) {
                    return $tsr->createQueryBuilder('ts')
                        ->innerJoin('ts.workshop', 'w')
                        ->where('ts.workshop = :workshopId')
                        ->andWhere('size(ts.reservations) < w.maxSlots')
                        ->andWhere('ts.date > :today')
                        ->setParameter('workshopId', $options['workshop_id'])
                        ->setParameter('today', new \DateTime('today'))
                        ->orderBy('ts.date', 'ASC')
                        ->addOrderBy('ts.fromTime', 'ASC');
                },
            ]);
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
