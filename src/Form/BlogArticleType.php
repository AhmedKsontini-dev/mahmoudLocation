<?php

namespace App\Form;

use App\Config\LocaleConfig;
use App\Entity\BlogArticle;
use App\Entity\BlogArticleTranslation;
use App\Entity\BlogCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BlogArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mainImageFile', FileType::class, [
                'label' => 'Image principale',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control', 'accept' => '.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp'],
                'constraints' => [
                    new Assert\Image(
                        maxSize: '5M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        extensions: ['jpg', 'jpeg', 'png', 'webp'],
                        maxSizeMessage: 'L’image ne doit pas dépasser 5 Mo.',
                        mimeTypesMessage: 'Veuillez sélectionner une image JPG, JPEG, PNG ou WebP.',
                        extensionsMessage: 'Veuillez sélectionner une image JPG, JPEG, PNG ou WebP.',
                        uploadIniSizeErrorMessage: 'Le fichier dépasse la limite autorisée par le serveur.',
                        uploadFormSizeErrorMessage: 'Le fichier dépasse la limite autorisée par le formulaire.',
                        uploadPartialErrorMessage: 'Le fichier image n’a été que partiellement téléversé. Réessayez.',
                        uploadErrorMessage: 'Le téléversement de l’image a échoué. Réessayez.',
                    ),
                ],
            ])
            ->add('deleteMainImage', CheckboxType::class, [
                'label' => 'Supprimer l\'image principale',
                'mapped' => false,
                'required' => false,
            ])
            ->add('imageAlt', TextType::class, [
                'label' => 'Texte alternatif',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('authorName', TextType::class, [
                'label' => 'Auteur',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('readTime', IntegerType::class, [
                'label' => 'Temps de lecture (minutes)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 1],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut*',
                'choices' => ['Brouillon' => 'draft', 'Publié' => 'published', 'Archivé' => 'archived'],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('isFeatured', CheckboxType::class, [
                'label' => 'Mettre en avant',
                'required' => false,
            ])
            ->add('publishedAt', DateTimeType::class, [
                'label' => 'Date de publication',
                'required' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('categories', EntityType::class, [
                'class' => BlogCategory::class,
                'choice_label' => fn (BlogCategory $category) => $category->getNameTranslated('fr') ?? $category->getSlug(),
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('galleryImages', CollectionType::class, [
                'entry_type' => BlogArticleImageType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'label' => false,
            ])
            ->add('videos', CollectionType::class, [
                'entry_type' => BlogArticleVideoType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'label' => false,
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => BlogArticleTranslationType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $article = $event->getData();
            if ($article && $article->getTranslations()->isEmpty()) {
                foreach (LocaleConfig::getSupportedLocales() as $locale) {
                    $translation = new BlogArticleTranslation();
                    $translation->setLocale($locale);
                    $article->addTranslation($translation);
                }
            }

            if ($article && $article->getGalleryImages()->isEmpty()) {
                $article->addGalleryImage(new \App\Entity\BlogArticleImage());
            }

            if ($article && $article->getVideos()->isEmpty()) {
                $article->addVideo(new \App\Entity\BlogArticleVideo());
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BlogArticle::class,
        ]);
    }
}
