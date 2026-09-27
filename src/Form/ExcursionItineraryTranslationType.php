<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ExcursionItineraryTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $translation = $event->getData();
            $form = $event->getForm();
            $locale = $translation ? $translation->getLocale() : 'fr';
            $isFrench = $locale === 'fr';

            $form
                ->add('title', TextType::class, [
                    'label' => 'Titre de l\'étape',
                    'attr' => ['class' => 'form-control'],
                    'required' => $isFrench,
                    'constraints' => $isFrench ? [
                        new Assert\NotBlank(['message' => 'Le titre est obligatoire en français.']),
                        new Assert\Length(['min' => 2, 'max' => 255]),
                    ] : [new Assert\Length(['min' => 2, 'max' => 255])],
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                    'required' => false,
                    'attr' => ['class' => 'form-control', 'rows' => 4],
                ])
            ;
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\ExcursionItineraryTranslation::class,
        ]);
    }
}
