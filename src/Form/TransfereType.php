<?php

namespace App\Form;

use App\Entity\Transfere;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Positive;

class TransfereType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prixNormal', NumberType::class, [
                'label' => 'Prix normal - reste de l’année',
                'scale' => 2,
                'html5' => true,
                'attr' => ['placeholder' => 'Ex. : 120'],
                'constraints' => [new Positive(message: 'Le prix normal doit être positif.')],
            ])
            ->add('prixSaisonForte', NumberType::class, [
                'label' => 'Prix saison forte - été',
                'scale' => 2,
                'html5' => true,
                'attr' => ['placeholder' => 'Ex. : 90'],
                'constraints' => [new Positive(message: 'Le prix saison forte doit être positif.')],
            ]);

        if ($options['show_description']) {
            $builder->add('description', TextareaType::class, ['label' => 'Description', 'required' => false]);
        }

        $builder
            ->add('actif', CheckboxType::class, ['label' => 'Transfert actif', 'required' => false])
            ->add('imageFile', FileType::class, [
                'label' => 'Image de la destination',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG, PNG, WebP)',
                    ])
                ],
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => TransfereTranslationType::class,
                'entry_options' => ['label' => false],
                'allow_add' => false,
                'allow_delete' => false,
                'by_reference' => false,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transfere::class,
            'show_description' => true,
        ]);
        $resolver->setAllowedTypes('show_description', 'bool');
    }
}