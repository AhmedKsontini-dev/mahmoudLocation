<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'profile.first_name',
                'required' => false,
                'attr' => ['autocomplete' => 'given-name', 'maxlength' => 100],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'profile.last_name',
                'required' => false,
                'attr' => ['autocomplete' => 'family-name', 'maxlength' => 100],
            ])
            ->add('phone', TelType::class, [
                'label' => 'profile.phone',
                'required' => false,
                'attr' => ['autocomplete' => 'tel', 'maxlength' => 50],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'profile.save',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
