<?php

namespace App\Form;

use App\Config\LocaleConfig;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\CategoryTranslation;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('parent', EntityType::class, [
                'label' => 'Catégorie parente',
                'class' => Category::class,
                'query_builder' => function (CategoryRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.position', 'ASC');
                },
                'choice_label' => function (Category $category) {
                    return $category->getNameTranslated('fr') ?? $category->getSlug();
                },
                'placeholder' => 'Aucune (catégorie racine)',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('icon', TextType::class, [
                'label' => 'Icône',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: 🏖️, 🏛️, 🌴'],
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Position',
                'attr' => ['class' => 'form-control', 'min' => 0],
                'data' => 0,
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Active',
                'required' => false,
                'data' => true,
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug (URL)',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^[a-z0-9-]+$/',
                        'message' => 'Le slug ne doit contenir que des lettres minuscules, des chiffres et des tirets.',
                    ]),
                ],
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => CategoryTranslationType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
            ])
        ;

        // Initialize translations for new entities
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $category = $event->getData();
            if ($category && $category->getTranslations()->isEmpty()) {
                $locales = LocaleConfig::getSupportedLocales();
                foreach ($locales as $locale) {
                    $translation = new CategoryTranslation();
                    $translation->setLocale($locale);
                    $category->addTranslation($translation);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
