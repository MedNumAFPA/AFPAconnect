<?php

namespace App\Form;

use App\Entity\Workshops;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkshopsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'atelier'
            ])
            ->add('description', TextType::class, [
                'label' => 'Description'
            ])
            ->add('maxSlots', IntegerType::class, [
                'label' => 'Places maximum'
            ])
            ->add('roomNumber', TextType::class, [
                'label' => 'N° de salle'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Workshops::class,
        ]);
    }
}
