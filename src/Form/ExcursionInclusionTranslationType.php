<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ExcursionInclusionTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $translation = $event->getData();
            $form = $event->getForm();
            $locale = $translation ? $translation->getLocale() : 'fr';
            $isFrench = $locale === 'fr';

            $form
                ->add('text', TextareaType::class, [
                    'label' => 'Texte',
                    'attr' => ['class' => 'form-control', 'rows' => 2],
                    'required' => $isFrench,
                    'constraints' => $isFrench ? [
                        new Assert\NotBlank(['message' => 'Le texte est obligatoire en français.']),
                        new Assert\Length(['min' => 2, 'max' => 500]),
                    ] : [new Assert\Length(['min' => 2, 'max' => 500])],
                ])
            ;
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\ExcursionInclusionTranslation::class,
        ]);
    }
}
