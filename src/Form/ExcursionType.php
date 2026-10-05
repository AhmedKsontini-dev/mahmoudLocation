<?php

namespace App\Form;

use App\Config\LocaleConfig;
use App\Entity\Category;
use App\Entity\Destination;
use App\Repository\CategoryRepository;
use App\Repository\DestinationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ExcursionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Informations générales
            ->add('category', EntityType::class, [
                'label' => 'Catégorie',
                'class' => Category::class,
                'query_builder' => function (CategoryRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.position', 'ASC');
                },
                'choice_label' => function (Category $category) {
                    return $category->getNameTranslated('fr') ?? $category->getSlug();
                },
                'placeholder' => '',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('destination', EntityType::class, [
                'label' => 'Destination',
                'class' => Destination::class,
                'query_builder' => function (DestinationRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->orderBy('d.id', 'ASC');
                },
                'choice_label' => function (Destination $destination) {
                    return $destination->getNameTranslated('fr') ?? $destination->getSlug();
                },
                'placeholder' => '',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Privé' => 'private',
                    'Groupe' => 'group',
                    'VIP' => 'vip',
                    'Personnalisé' => 'custom',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Actif' => 'active',
                    'Inactif' => 'inactive',
                    'Brouillon' => 'draft',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('isBestSeller', CheckboxType::class, [
                'label' => 'Best Seller',
                'required' => false,
            ])
            ->add('featured', CheckboxType::class, [
                'label' => 'Mis en avant',
                'required' => false,
            ])
            
            // Durée et horaires
            ->add('durationHours', IntegerType::class, [
                'label' => 'Durée (heures)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 0],
            ])
            ->add('durationMinutes', IntegerType::class, [
                'label' => 'Durée (minutes)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 0, 'max' => 59],
            ])
            ->add('startTime', TimeType::class, [
                'label' => 'Heure de début',
                'required' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('endTime', TimeType::class, [
                'label' => 'Heure de fin',
                'required' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            
            // Prix
            ->add('priceAdult', NumberType::class, [
                'label' => 'Prix adulte',
                'scale' => 2,
                'required' => true,
                'attr' => ['class' => 'form-control', 'step' => '0.01', 'min' => '0.01'],
            ])
            ->add('priceChild', NumberType::class, [
                'label' => 'Prix enfant',
                'scale' => 2,
                'required' => false,
                'attr' => ['class' => 'form-control', 'step' => '0.01'],
            ])
            ->add('priceConverted', NumberType::class, [
                'label' => 'Prix converti',
                'scale' => 2,
                'required' => false,
                'attr' => ['class' => 'form-control', 'step' => '0.01'],
            ])
            
            // Participants
            ->add('minParticipants', IntegerType::class, [
                'label' => 'Participants minimum',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 1],
            ])
            ->add('maxParticipants', IntegerType::class, [
                'label' => 'Participants maximum',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 1],
            ])
            
            // Annulation
            ->add('cancellationHours', IntegerType::class, [
                'label' => 'Heures d\'annulation gratuite',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 0],
            ])
            
            // Slug
            ->add('slug', TextType::class, [
                'label' => 'Slug (URL)',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^[a-z0-9-]+$/',
                        'message' => 'Le slug ne doit contenir que des lettres minuscules, des chiffres et des tirets.',
                    ]),
                ],
            ])
            
            // Traductions
            ->add('translations', CollectionType::class, [
                'entry_type' => ExcursionTranslationType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
            ]);
            
            // Itinéraire
            $itineraryPrototype = new \App\Entity\ExcursionItinerary();
            foreach (LocaleConfig::getSupportedLocales() as $locale) {
                $trans = new \App\Entity\ExcursionItineraryTranslation();
                $trans->setLocale($locale);
                $itineraryPrototype->addTranslation($trans);
            }

            $builder->add('itineraries', CollectionType::class, [
                'entry_type' => ExcursionItineraryType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'prototype' => true,
                'prototype_data' => $itineraryPrototype,
            ])
            
            // Images
            ->add('images', CollectionType::class, [
                'entry_type' => ExcursionImageType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'prototype' => true,
            ]);
            
            // Features
            $featurePrototype = new \App\Entity\ExcursionFeature();
            foreach (LocaleConfig::getSupportedLocales() as $locale) {
                $trans = new \App\Entity\ExcursionFeatureTranslation();
                $trans->setLocale($locale);
                $featurePrototype->addTranslation($trans);
            }

            $builder->add('features', CollectionType::class, [
                'entry_type' => ExcursionFeatureType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'prototype' => true,
                'prototype_data' => $featurePrototype,
            ]);
            
            // Inclusions
            $inclusionPrototype = new \App\Entity\ExcursionInclusion();
            foreach (LocaleConfig::getSupportedLocales() as $locale) {
                $trans = new \App\Entity\ExcursionInclusionTranslation();
                $trans->setLocale($locale);
                $inclusionPrototype->addTranslation($trans);
            }

            $builder->add('inclusions', CollectionType::class, [
                'entry_type' => ExcursionInclusionType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'prototype' => true,
                'prototype_data' => $inclusionPrototype,
            ]);
            
            // FAQs
            $faqPrototype = new \App\Entity\ExcursionFaq();
            foreach (LocaleConfig::getSupportedLocales() as $locale) {
                $trans = new \App\Entity\ExcursionFaqTranslation();
                $trans->setLocale($locale);
                $faqPrototype->addTranslation($trans);
            }

            $builder->add('faqs', CollectionType::class, [
                'entry_type' => ExcursionFaqType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'prototype' => true,
                'prototype_data' => $faqPrototype,
            ]);
            
            // Badges
            $badgePrototype = new \App\Entity\ExcursionBadge();
            foreach (LocaleConfig::getSupportedLocales() as $locale) {
                $trans = new \App\Entity\ExcursionBadgeTranslation();
                $trans->setLocale($locale);
                $badgePrototype->addTranslation($trans);
            }

            $builder->add('badges', CollectionType::class, [
                'entry_type' => ExcursionBadgeType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'prototype' => true,
                'prototype_data' => $badgePrototype,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\Excursion::class,
            'is_new' => false,
        ]);
    }
}
