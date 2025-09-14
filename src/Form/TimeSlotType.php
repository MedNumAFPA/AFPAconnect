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
            ->add('fromTime', TimeType::class, [
                'label' => 'Heure de début',
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('toTime', TimeType::class, [
                'label' => 'Heure de fin',
                'widget' => 'single_text',
                'html5' => true,
            ]);

        if (isset($options['default_from_time'])) {
            $fromTimeOptions['data'] = $options['default_from_time'];
            $builder->add('fromTime', TimeType::class, $fromTimeOptions

        );
        }
        if (isset($options['default_to_time'])) {
            $toTimeOptions['data'] = $options['default_to_time'];
            $builder->add('toTime', TimeType::class, $toTimeOptions);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TimeSlot::class,
            'default_from_time' => null,
            'default_to_time' => null,
        ]);
    }
}
