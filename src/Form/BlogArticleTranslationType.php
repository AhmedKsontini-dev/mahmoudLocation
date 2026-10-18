<?php

namespace App\Form;

use App\Entity\BlogArticleTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlogArticleTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug SEO',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('excerpt', TextareaType::class, [
                'label' => 'Extrait',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'required' => false,
                'attr' => ['class' => 'form-control rich-text-editor-input', 'rows' => 12],
            ])
            ->add('seoTitle', TextType::class, [
                'label' => 'Titre SEO',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('seoDescription', TextareaType::class, [
                'label' => 'Description SEO',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3],
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, static function (FormEvent $event): void {
            $translation = $event->getData();
            if (!$translation instanceof BlogArticleTranslation) {
                return;
            }

            $locale = $translation->getLocale();
            $localeLabel = match ($locale) {
                'fr' => 'français',
                'en' => 'anglais',
                'es' => 'espagnol',
                'de' => 'allemand',
                'it' => 'italien',
                default => $locale,
            };
            $hasTitle = trim((string) $translation->getTitle()) !== '';
            $contentText = html_entity_decode(strip_tags((string) $translation->getContent()), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $hasContent = trim(str_replace("\u{00A0}", ' ', $contentText)) !== '';

            if ($locale === 'fr' && !$hasTitle) {
                $event->getForm()->get('title')->addError(new FormError('Le titre en français est obligatoire.'));
            }
            if ($locale === 'fr' && !$hasContent) {
                $event->getForm()->get('content')->addError(new FormError('Le contenu en français est obligatoire.'));
            }

            if ($locale !== 'fr' && $hasTitle && !$hasContent) {
                $event->getForm()->get('content')->addError(new FormError(sprintf('Le contenu en %s est obligatoire lorsque le titre est renseigné.', $localeLabel)));
            } elseif ($locale !== 'fr' && $hasContent && !$hasTitle) {
                $event->getForm()->get('title')->addError(new FormError(sprintf('Le titre en %s est obligatoire lorsque le contenu est renseigné.', $localeLabel)));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BlogArticleTranslation::class,
        ]);
    }
}
