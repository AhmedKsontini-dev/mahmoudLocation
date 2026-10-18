<?php

namespace App\Form;

use App\Entity\BlogArticleVideo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BlogArticleVideoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Libellé',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('externalUrl', UrlType::class, [
                'label' => 'URL vidéo externe',
                'required' => false,
                'default_protocol' => 'https',
                'invalid_message' => 'Veuillez saisir une URL vidéo valide.',
                'attr' => ['class' => 'form-control', 'placeholder' => 'https://www.youtube.com/watch?...'],
            ])
            ->add('videoFile', FileType::class, [
                'label' => 'Vidéo locale',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\File(
                        maxSize: '50M',
                        mimeTypes: ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo', 'video/mpeg'],
                        extensions: ['mp4', 'webm', 'mov', 'avi', 'm4v', 'mpeg'],
                        maxSizeMessage: 'La vidéo ne doit pas dépasser 50 Mo.',
                        mimeTypesMessage: 'Veuillez choisir une vidéo au format MP4, WebM, MOV, AVI ou MPEG.',
                        extensionsMessage: 'Veuillez choisir une vidéo au format MP4, WebM, MOV, AVI ou MPEG.',
                        uploadIniSizeErrorMessage: 'La vidéo dépasse la limite autorisée par le serveur.',
                        uploadFormSizeErrorMessage: 'La vidéo dépasse la limite autorisée par le formulaire.',
                        uploadPartialErrorMessage: 'La vidéo n’a été que partiellement téléversée. Réessayez.',
                        uploadErrorMessage: 'Le téléversement de la vidéo a échoué. Réessayez.',
                    ),
                ],
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
            'data_class' => BlogArticleVideo::class,
        ]);
    }
}
