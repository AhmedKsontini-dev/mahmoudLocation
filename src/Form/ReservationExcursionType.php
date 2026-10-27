<?php

namespace App\Form;

use App\Entity\Excursion;
use App\Entity\ReservationExcursion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationExcursionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $inputClass = 'w-full h-11 px-4 rounded-lg bg-surface-container-lowest border border-surface-container text-on-surface focus:ring-2 focus:ring-primary outline-none';
        $labelClass = 'block font-label-md text-label-md text-on-surface-variant mb-2';

        // Limite maximale de participants définie par l'excursion (null si aucune excursion transmise).
        $maxParticipants = $options['excursion']?->getMaxParticipants();

        $builder
            ->add('dateExcursion', DateType::class, [
                'label' => 'excursion.detail.form.date',
                'label_attr' => ['class' => $labelClass],
                'widget' => 'single_text',
                'attr' => ['class' => $inputClass, 'min' => (new \DateTimeImmutable('today'))->format('Y-m-d')],
            ])
            ->add('heureDepart', TimeType::class, [
                'label' => 'excursion.detail.form.departure_time',
                'label_attr' => ['class' => $labelClass],
                'widget' => 'single_text',
                'attr' => ['class' => $inputClass],
            ])
            ->add('nombreAdultes', IntegerType::class, [
                'label' => 'excursion.detail.form.adults',
                'label_attr' => ['class' => $labelClass],
                'attr' => ['class' => $inputClass, 'min' => 1, 'max' => $maxParticipants],
            ])
            ->add('nombreEnfants', IntegerType::class, [
                'label' => 'excursion.detail.form.children',
                'label_attr' => ['class' => $labelClass],
                'attr' => ['class' => $inputClass, 'min' => 0, 'max' => $maxParticipants],
            ])
            ->add('lieuPriseEnCharge', TextType::class, [
                'label' => 'excursion.detail.form.pickup',
                'label_attr' => ['class' => $labelClass],
                'attr' => ['class' => $inputClass, 'placeholder' => 'excursion.detail.form.pickup_placeholder'],
            ])
            ->add('nom', TextType::class, [
                'label' => 'excursion.detail.form.last_name',
                'label_attr' => ['class' => $labelClass],
                'attr' => ['class' => $inputClass, 'autocomplete' => 'family-name'],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'excursion.detail.form.first_name',
                'label_attr' => ['class' => $labelClass],
                'attr' => ['class' => $inputClass, 'autocomplete' => 'given-name'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'excursion.detail.form.email',
                'label_attr' => ['class' => $labelClass],
                'attr' => ['class' => $inputClass, 'autocomplete' => 'email'],
            ])
            ->add('whatsapp', TextType::class, [
                'label' => 'excursion.detail.form.whatsapp',
                'label_attr' => ['class' => $labelClass],
                'attr' => [
                    'class' => $inputClass,
                    'type' => 'tel',
                    'inputmode' => 'tel',
                    'autocomplete' => 'tel',
                    'placeholder' => '+216 12 345 678',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReservationExcursion::class,
            'validation_groups' => ['Default', 'frontend_booking'],
            'excursion' => null,
        ]);
        $resolver->setAllowedTypes('excursion', ['null', Excursion::class]);
    }
}
