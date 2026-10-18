<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use App\Config\LocaleConfig;
use App\Form\EventListener\AlignSubmittedTranslationsByLocaleListener;
use App\Entity\ExcursionFeatureTranslation;

class ExcursionFeatureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('icon', TextType::class, [
                'label' => 'Icône',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: 🚗, ✈️, 🏖️'],
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Position',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => ExcursionFeatureTranslationType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'prototype' => true,
            ])
        ;

        // La soumission est réalignée par locale sur l'ordre réel de la collection
        // (l'ordre de chargement Doctrine n'est pas garanti).
        $builder->get('translations')->addEventSubscriber(new AlignSubmittedTranslationsByLocaleListener());

        // Initialize translations for new entities
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $feature = $event->getData();
            if ($feature && $feature->getTranslations()->isEmpty()) {
                $locales = LocaleConfig::getExcursionFormLocales();
                foreach ($locales as $locale) {
                    $translation = new ExcursionFeatureTranslation();
                    $translation->setLocale($locale);
                    $feature->addTranslation($translation);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\ExcursionFeature::class,
        ]);
    }
}