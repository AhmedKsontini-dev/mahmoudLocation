<?php

namespace App\Form;

use App\Config\LocaleConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ExcursionTranslationType extends AbstractType
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
                    'label' => 'Titre',
                    'attr' => ['class' => 'form-control'],
                    'required' => false,
                    'constraints' => $isFrench ? [
                        new Assert\NotBlank(['message' => 'Le titre est obligatoire en français.']),
                        new Assert\Length(['min' => 2, 'max' => 255]),
                    ] : [new Assert\Length(['min' => 2, 'max' => 255])],
                ])
                ->add('subtitle', TextType::class, [
                    'label' => 'Sous-titre',
                    'required' => false,
                    'attr' => ['class' => 'form-control'],
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                    'attr' => ['class' => 'form-control', 'rows' => 6],
                    'required' => false,
                    'constraints' => $isFrench ? [
                        new Assert\NotBlank(['message' => 'La description est obligatoire en français.']),
                    ] : [],
                ])
                ->add('location', TextType::class, [
                    'label' => 'Lieu',
                    'required' => false,
                    'attr' => ['class' => 'form-control'],
                ])
                ->add('metaTitle', TextType::class, [
                    'label' => 'Meta titre',
                    'required' => false,
                    'attr' => ['class' => 'form-control'],
                ])
                ->add('metaDescription', TextareaType::class, [
                    'label' => 'Meta description',
                    'required' => false,
                    'attr' => ['class' => 'form-control', 'rows' => 3],
                ])
            ;
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\ExcursionTranslation::class,
        ]);
    }
}
