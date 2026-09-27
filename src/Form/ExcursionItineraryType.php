<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use App\Config\LocaleConfig;
use App\Entity\ExcursionItineraryTranslation;

class ExcursionItineraryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('stepNumber', IntegerType::class, [
                'label' => 'Numéro d\'étape',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Positive(),
                ],
            ])
            ->add('time', TimeType::class, [
                'label' => 'Heure',
                'required' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Durée (minutes)',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Position',
                'attr' => ['class' => 'form-control'],
                'data' => 0,
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => ExcursionItineraryTranslationType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'prototype' => true,
            ])
        ;

        // Initialize translations for new entities
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $itinerary = $event->getData();
            if ($itinerary && $itinerary->getTranslations()->isEmpty()) {
                $locales = LocaleConfig::getSupportedLocales();
                foreach ($locales as $locale) {
                    $translation = new ExcursionItineraryTranslation();
                    $translation->setLocale($locale);
                    $itinerary->addTranslation($translation);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\ExcursionItinerary::class,
        ]);
    }
}
