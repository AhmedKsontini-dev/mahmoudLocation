<?php

namespace App\Form;

use App\Entity\Saison;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SaisonType extends AbstractType
{
    private const MONTHS = [
        'Janvier' => 1,
        'Février' => 2,
        'Mars' => 3,
        'Avril' => 4,
        'Mai' => 5,
        'Juin' => 6,
        'Juillet' => 7,
        'Août' => 8,
        'Septembre' => 9,
        'Octobre' => 10,
        'Novembre' => 11,
        'Décembre' => 12,
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $days = [];
        for ($day = 1; $day <= 31; $day++) {
            $days[$day] = $day;
        }

        $builder
            ->add('nom', TextType::class, ['label' => 'Nom de la saison'])
            ->add('dateDebutJour', ChoiceType::class, [
                'label' => 'Jour de début',
                'choices' => $days,
                'placeholder' => '—',
            ])
            ->add('dateDebutMois', ChoiceType::class, [
                'label' => 'Mois de début',
                'choices' => self::MONTHS,
                'placeholder' => '—',
            ])
            ->add('dateFinJour', ChoiceType::class, [
                'label' => 'Jour de fin',
                'choices' => $days,
                'placeholder' => '—',
            ])
            ->add('dateFinMois', ChoiceType::class, [
                'label' => 'Mois de fin',
                'choices' => self::MONTHS,
                'placeholder' => '—',
            ])
            ->add('actif', CheckboxType::class, ['label' => 'Saison active', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Saison::class]);
    }
}
