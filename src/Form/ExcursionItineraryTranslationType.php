<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ExcursionItineraryTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // La locale identifie la traduction (contrainte UNIQUE parent+locale) :
        // elle voyage dans un champ caché pour survivre à la recréation des
        // entrées ajoutées via le prototype.
        $configureFields = static function (FormInterface $form, string $locale): void {
            $isFrench = $locale === 'fr';

            $form
                ->add('locale', HiddenType::class, [
                    'label' => false,
                    // Une soumission qui ne poste pas de locale (onglet ouvert avant
                    // l'existence de ce champ caché) ne doit jamais écrire null dans
                    // la propriété typée : la traduction conserve sa propre identité,
                    // sinon la locale par défaut du projet.
                    'empty_data' => static function (FormInterface $form): string {
                        $translation = $form->getParent()?->getData();
                        $locale = \is_object($translation) && method_exists($translation, 'getLocale') ? $translation->getLocale() : null;
                        return \is_string($locale) && '' !== $locale ? $locale : 'fr';
                    },
                ])
                ->add('title', TextType::class, [
                    'label' => 'Titre de l\'étape',
                    'attr' => ['class' => 'form-control'],
                    'required' => $isFrench,
                    'constraints' => $isFrench ? [
                        new Assert\NotBlank(['message' => 'Le titre est obligatoire en français.']),
                        new Assert\Length(['min' => 2, 'max' => 255]),
                    ] : [new Assert\Length(['max' => 255])],
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                    'required' => false,
                    'attr' => ['class' => 'form-control', 'rows' => 4],
                ])
            ;
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($configureFields) {
            $translation = $event->getData();
            $locale = $translation ? $translation->getLocale() : 'fr';
            $configureFields($event->getForm(), $locale);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($configureFields) {
            $data = $event->getData();
            if (\is_array($data) && isset($data['locale']) && \is_string($data['locale']) && $data['locale'] !== '') {
                $configureFields($event->getForm(), $data['locale']);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\ExcursionItineraryTranslation::class,
        ]);
    }
}