<?php

namespace App\Form;

use App\Entity\ReservationTransfere;
use App\Enum\ReservationTransfereType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationTransfereFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $inputClass = 'w-full h-11 px-3.5 rounded-xl border border-surface-container-high bg-surface-container-low/30 text-xs md:text-sm font-medium focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all';
        $labelClass = 'block text-xs font-semibold text-on-surface mb-1';

        $builder
            ->add('transfereType', ChoiceType::class, ['choices' => ['transfer.form.one_way' => ReservationTransfereType::ONE_WAY, 'transfer.form.return' => ReservationTransfereType::RETURN], 'choice_value' => static fn (?ReservationTransfereType $choice): ?string => $choice?->value, 'expanded' => false, 'attr' => ['class' => 'hidden']])
            ->add('pickupLocation', TextType::class, ['label' => 'transfer.form.pickup_location', 'label_attr' => ['class' => $labelClass], 'attr' => ['class' => $inputClass]])
            ->add('dropoffLocation', TextType::class, ['label' => 'transfer.form.dropoff_location', 'label_attr' => ['class' => $labelClass], 'attr' => ['class' => $inputClass]])
            ->add('pickupDate', DateType::class, ['label' => 'transfer.form.pickup_date', 'label_attr' => ['class' => $labelClass], 'widget' => 'single_text', 'input' => 'datetime_immutable', 'attr' => ['class' => $inputClass]])
            ->add('pickupTime', TimeType::class, ['label' => 'transfer.form.pickup_time', 'label_attr' => ['class' => $labelClass], 'widget' => 'single_text', 'input' => 'datetime_immutable', 'attr' => ['class' => $inputClass]])
            ->add('returnPickupLocation', TextType::class, ['label' => 'transfer.form.return_pickup_location', 'label_attr' => ['class' => $labelClass], 'required' => false, 'attr' => ['class' => $inputClass]])
            ->add('returnDropoffLocation', TextType::class, ['label' => 'transfer.form.return_dropoff_location', 'label_attr' => ['class' => $labelClass], 'required' => false, 'attr' => ['class' => $inputClass]])
            ->add('returnPickupDate', DateType::class, ['label' => 'transfer.form.return_date', 'label_attr' => ['class' => $labelClass], 'required' => false, 'widget' => 'single_text', 'input' => 'datetime_immutable', 'attr' => ['class' => $inputClass]])
            ->add('returnPickupTime', TimeType::class, ['label' => 'transfer.form.return_time', 'label_attr' => ['class' => $labelClass], 'required' => false, 'widget' => 'single_text', 'input' => 'datetime_immutable', 'attr' => ['class' => $inputClass]])
            ->add('nom', TextType::class, ['label' => 'transfer.form.full_name', 'label_attr' => ['class' => $labelClass], 'attr' => ['class' => $inputClass]])
            ->add('email', EmailType::class, ['label' => 'transfer.form.email', 'label_attr' => ['class' => $labelClass], 'attr' => ['class' => $inputClass]])
            ->add('telephone', TextType::class, ['label' => 'transfer.form.phone', 'label_attr' => ['class' => $labelClass], 'attr' => ['class' => $inputClass]])
            ->add('nombrePassagers', IntegerType::class, ['label' => 'transfer.form.passengers', 'empty_data' => '1', 'attr' => ['min' => 1, 'class' => 'sr-only']])
            ->add('nombreBagages', IntegerType::class, ['label' => 'transfer.form.luggage', 'empty_data' => '0', 'attr' => ['min' => 0, 'class' => 'sr-only']])
            ->add('compagnieVol', TextType::class, ['label' => 'transfer.form.airline', 'label_attr' => ['class' => $labelClass], 'required' => false, 'attr' => ['class' => $inputClass, 'placeholder' => 'Ex: Tunisair']])
            ->add('numeroVol', TextType::class, ['label' => 'transfer.form.flight_number', 'label_attr' => ['class' => $labelClass], 'required' => false, 'attr' => ['class' => $inputClass, 'placeholder' => 'Ex: TU123']])
            ->add('message', TextareaType::class, ['label' => 'transfer.form.message', 'label_attr' => ['class' => $labelClass], 'required' => false, 'attr' => ['class' => $inputClass.' min-h-24']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ReservationTransfere::class]);
    }
}