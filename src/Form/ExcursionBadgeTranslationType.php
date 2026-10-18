<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExcursionBadgeTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // La locale identifie la traduction (contrainte UNIQUE parent+locale) :
        // elle voyage dans un champ caché pour que les entrées ajoutées via le
        // prototype conservent leur locale à la soumission.
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
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
                ->add('text', TextType::class, [
                    'label' => 'Texte du badge',
                    'required' => false,
                    'attr' => ['class' => 'form-control'],
                ])
            ;
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\ExcursionBadgeTranslation::class,
        ]);
    }
}
