<?php

namespace App\Form;

use App\Config\LocaleConfig;
use App\Entity\Destination;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\DestinationTranslation;

class DestinationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('region', TextType::class, [
                'label' => 'Région',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude',
                'required' => false,
                'scale' => 8,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 36.806389',
                    'step' => '0.00000001',
                    'min' => -90,
                    'max' => 90,
                ],
                'constraints' => [
                    new Assert\Range([
                        'min' => -90,
                        'max' => 90,
                        'minMessage' => 'La latitude doit être entre -90 et 90.',
                        'maxMessage' => 'La latitude doit être entre -90 et 90.',
                    ]),
                ],
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude',
                'required' => false,
                'scale' => 8,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 10.181667',
                    'step' => '0.00000001',
                    'min' => -180,
                    'max' => 180,
                ],
                'constraints' => [
                    new Assert\Range([
                        'min' => -180,
                        'max' => 180,
                        'minMessage' => 'La longitude doit être entre -180 et 180.',
                        'maxMessage' => 'La longitude doit être entre -180 et 180.',
                    ]),
                ],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Active',
                'required' => false,
                'data' => true,
            ])
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
            ->add('translations', CollectionType::class, [
                'entry_type' => DestinationTranslationType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
            ])
        ;

        // Initialize translations for new entities
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $destination = $event->getData();
            if ($destination && $destination->getTranslations()->isEmpty()) {
                $locales = LocaleConfig::getSupportedLocales();
                foreach ($locales as $locale) {
                    $translation = new DestinationTranslation();
                    $translation->setLocale($locale);
                    $destination->addTranslation($translation);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Destination::class,
        ]);
    }
}
