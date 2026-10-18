<?php

namespace App\Form;

use App\Entity\Excursion;
use App\Entity\ReservationExcursion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationExcursionAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du client',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom de famille',
                    'autocomplete' => 'family-name',
                ],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom du client',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Prénom',
                    'autocomplete' => 'given-name',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'client@exemple.com',
                    'autocomplete' => 'email',
                ],
            ])
            ->add('whatsapp', TextType::class, [
                'label' => 'Numéro WhatsApp',
                'required' => true,
                'help' => 'Exemple : +216 12 345 678 ou +33 6 12 34 56 78',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '+216 12 345 678',
                    'autocomplete' => 'tel',
                ],
            ])
            ->add('excursion', EntityType::class, [
                'class' => Excursion::class,
                'label' => 'Excursion réservée',
                'required' => true,
                'choice_label' => function (Excursion $excursion): string {
                    $title = $excursion->getTitleTranslated('fr');
                    return $title ? sprintf('%s (#%d)', $title, $excursion->getId()) : sprintf('Excursion #%d (%s)', $excursion->getId(), $excursion->getSlug());
                },
                'placeholder' => '— Sélectionnez une excursion —',
                'attr' => [
                    'class' => 'form-control form-select',
                ],
            ])
            ->add('nombreAdultes', IntegerType::class, [
                'label' => 'Nombre d’adultes',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                ],
            ])
            ->add('nombreEnfants', IntegerType::class, [
                'label' => 'Nombre d’enfants',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                ],
            ])
            ->add('dateExcursion', DateType::class, [
                'label' => 'Date de l’excursion',
                'widget' => 'single_text',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('heureDepart', TimeType::class, [
                'label' => 'Heure de départ',
                'widget' => 'single_text',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('lieuPriseEnCharge', TextareaType::class, [
                'label' => 'Lieu de prise en charge',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Hôtel, résidence, aéroport ou point de rendez-vous convenu...',
                ],
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut de la réservation',
                'required' => true,
                'choices' => ReservationExcursion::getAvailableStatuses(),
                'attr' => [
                    'class' => 'form-control form-select',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReservationExcursion::class,
            'validation_groups' => ['Default'],
        ]);
    }
}
