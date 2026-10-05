<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use App\Config\LocaleConfig;
use App\Entity\ExcursionImageTranslation;

class ExcursionImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imageFile', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control', 'accept' => '.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp'],
                'constraints' => [
                    new Assert\Image([
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG, WebP).',
                    ]),
                ],
            ])
            ->add('imageAlt', TextType::class, [
                'label' => 'Texte alternatif',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Position',
                'attr' => ['class' => 'form-control'],
                'data' => 0,
            ])
            ->add('isMain', CheckboxType::class, [
                'label' => 'Image principale',
                'required' => false,
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => ExcursionImageTranslationType::class,
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
            $image = $event->getData();
            if ($image && $image->getTranslations()->isEmpty()) {
                $locales = LocaleConfig::getSupportedLocales();
                foreach ($locales as $locale) {
                    $translation = new ExcursionImageTranslation();
                    $translation->setLocale($locale);
                    $image->addTranslation($translation);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\ExcursionImage::class,
        ]);
    }
}
