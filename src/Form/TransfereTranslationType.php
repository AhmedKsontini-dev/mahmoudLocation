<?php

namespace App\Form;

use App\Entity\TransfereTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransfereTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lieuDepart', TextType::class, [
                'label' => 'Lieu de départ',
                'required' => false,
            ])
            ->add('lieuArrivee', TextType::class, [
                'label' => "Lieu d'arrivée",
                'required' => false,
            ])
            ->add('duree', TextType::class, [
                'label' => 'Durée',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TransfereTranslation::class,
            'locale' => 'fr',
        ]);
        $resolver->setAllowedTypes('locale', 'string');
    }
}
