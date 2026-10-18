<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Config\LocaleConfig;
use App\Form\EventListener\AlignSubmittedTranslationsByLocaleListener;
use App\Entity\ExcursionBadgeTranslation;

class ExcursionBadgeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('badgeType', ChoiceType::class, [
                'label' => 'Type de badge',
                'choices' => [
                    'Best Seller' => 'best_seller',
                    'Disponible maintenant' => 'available_now',
                    'Guide privé' => 'private_guided',
                    'Personnalisé' => 'custom',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Active',
                'required' => false,
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => ExcursionBadgeTranslationType::class,
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
            $badge = $event->getData();
            if ($badge && $badge->getTranslations()->isEmpty()) {
                $locales = LocaleConfig::getExcursionFormLocales();
                foreach ($locales as $locale) {
                    $translation = new ExcursionBadgeTranslation();
                    $translation->setLocale($locale);
                    $badge->addTranslation($translation);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\ExcursionBadge::class,
        ]);
    }
}