<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'contact.form.first_name',
                'attr' => [
                    'class' => 'w-full h-12 px-4 rounded-xl bg-surface-container-low text-on-surface placeholder:text-outline/70 focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary font-body-md text-body-md transition-all',
                    'placeholder' => 'contact.form.first_name_placeholder'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'contact.form.first_name_required']),
                    new Length(['min' => 2, 'max' => 100])
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'contact.form.last_name',
                'attr' => [
                    'class' => 'w-full h-12 px-4 rounded-xl bg-surface-container-low text-on-surface placeholder:text-outline/70 focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary font-body-md text-body-md transition-all',
                    'placeholder' => 'contact.form.last_name_placeholder'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'contact.form.last_name_required']),
                    new Length(['min' => 2, 'max' => 100])
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'contact.form.email',
                'attr' => [
                    'class' => 'w-full h-12 px-4 rounded-xl bg-surface-container-low text-on-surface placeholder:text-outline/70 focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary font-body-md text-body-md transition-all',
                    'placeholder' => 'contact.form.email_placeholder'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'contact.form.email_required']),
                    new Email(['message' => 'contact.form.email_invalid'])
                ]
            ])
            ->add('phone', TextType::class, [
                'label' => 'contact.form.phone',
                'attr' => [
                    'class' => 'w-full h-12 px-4 rounded-xl bg-surface-container-low text-on-surface placeholder:text-outline/70 focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary font-body-md text-body-md transition-all',
                    'placeholder' => 'contact.form.phone_placeholder'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'contact.form.phone_required']),
                    new Length(['min' => 8, 'max' => 20])
                ]
            ])
            ->add('requestType', ChoiceType::class, [
                'label' => 'contact.form.request_type',
                'choices' => [
                    'contact.form.type_information' => 'information',
                    'contact.form.type_reservation' => 'reservation',
                    'contact.form.type_group_quote' => 'group_quote',
                    'contact.form.type_partnership' => 'partnership'
                ],
                'attr' => [
                    'class' => 'w-full h-12 px-4 pr-10 rounded-xl bg-surface-container-low text-on-surface focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary font-body-md text-body-md appearance-none transition-all cursor-pointer'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'contact.form.request_type_required'])
                ]
            ])
            ->add('serviceCategory', ChoiceType::class, [
                'label' => 'contact.form.service_category',
                'choices' => [
                    'contact.form.service_location' => 'location',
                    'contact.form.service_transfer' => 'transfert',
                    'contact.form.service_excursion' => 'excursion',
                    'contact.form.service_group' => 'groupe',
                    'contact.form.service_other' => 'autre'
                ],
                'attr' => [
                    'class' => 'w-full h-12 px-4 pr-10 rounded-xl bg-surface-container-low text-on-surface focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary font-body-md text-body-md appearance-none transition-all cursor-pointer'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'contact.form.service_category_required'])
                ]
            ])
            ->add('reservationNumber', TextType::class, [
                'label' => 'contact.form.reservation_number',
                'attr' => [
                    'class' => 'w-full h-12 px-4 rounded-xl bg-surface-container-low text-on-surface placeholder:text-outline/70 focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary font-body-md text-body-md transition-all',
                    'placeholder' => 'contact.form.reservation_number_placeholder'
                ],
                'required' => false
            ])
            ->add('subject', TextType::class, [
                'label' => 'contact.form.subject',
                'attr' => [
                    'class' => 'w-full h-12 px-4 rounded-xl bg-surface-container-low text-on-surface placeholder:text-outline/70 focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary font-body-md text-body-md transition-all',
                    'placeholder' => 'contact.form.subject_placeholder'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'contact.form.subject_required']),
                    new Length(['min' => 3, 'max' => 200])
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'contact.form.message',
                'attr' => [
                    'class' => 'w-full p-4 rounded-xl bg-surface-container-low text-on-surface placeholder:text-outline/70 focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary font-body-md text-body-md transition-all',
                    'placeholder' => 'contact.form.message_placeholder',
                    'rows' => 4
                ],
                'constraints' => [
                    new NotBlank(['message' => 'contact.form.message_required']),
                    new Length(['min' => 10, 'max' => 2000])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\Contact::class,
            'translation_domain' => 'messages',
        ]);
    }
}
