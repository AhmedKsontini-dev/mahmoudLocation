<?php

namespace App\Form;

use App\Entity\BlogCategoryTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BlogCategoryTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $translation = $options['data'] ?? null;
        $isPrimaryLocale = $translation instanceof BlogCategoryTranslation && $translation->getLocale() === 'fr';

        $builder
            ->add('name', TextType::class, [
                'label' => $isPrimaryLocale ? 'Nom*' : 'Nom',
                'required' => $isPrimaryLocale,
                'attr' => ['class' => 'form-control'],
                'constraints' => $isPrimaryLocale
                    ? [new Assert\NotBlank(message: 'Le nom de la catégorie en français est obligatoire.')]
                    : [],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 4],
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, static function (FormEvent $event): void {
            $translation = $event->getData();
            $form = $event->getForm();

            if (
                $translation instanceof BlogCategoryTranslation
                && $translation->getLocale() !== 'fr'
                && trim((string) $translation->getName()) === ''
                && trim((string) $translation->getDescription()) !== ''
            ) {
                $form->get('name')->addError(new FormError('Renseignez un nom pour cette langue avant d’ajouter une description.'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BlogCategoryTranslation::class,
        ]);
    }
}
