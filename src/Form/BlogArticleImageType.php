<?php

namespace App\Form;

use App\Entity\BlogArticleImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BlogArticleImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imageFile', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\Image(
                        maxSize: '5M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        extensions: ['jpg', 'jpeg', 'png', 'webp'],
                        maxSizeMessage: 'L’image ne doit pas dépasser 5 Mo.',
                        mimeTypesMessage: 'Veuillez choisir une image JPG, JPEG, PNG ou WebP.',
                        extensionsMessage: 'Veuillez choisir une image JPG, JPEG, PNG ou WebP.',
                        uploadIniSizeErrorMessage: 'Le fichier dépasse la limite autorisée par le serveur.',
                        uploadFormSizeErrorMessage: 'Le fichier dépasse la limite autorisée par le formulaire.',
                        uploadPartialErrorMessage: 'Le fichier image n’a été que partiellement téléversé. Réessayez.',
                        uploadErrorMessage: 'Le téléversement de l’image a échoué. Réessayez.',
                    ),
                ],
            ])
            ->add('imageAlt', TextType::class, [
                'label' => 'Texte alternatif',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Position',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('delete', CheckboxType::class, [
                'label' => 'Supprimer',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BlogArticleImage::class,
        ]);
    }
}
