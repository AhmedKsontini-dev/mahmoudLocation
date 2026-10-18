<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AdminContactMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('recipient', EmailType::class, [
                'label' => 'Destinataire (À)',
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez saisir l’adresse e-mail du destinataire.'),
                    new Assert\Email(message: 'Veuillez saisir une adresse e-mail valide.'),
                    new Assert\Length(max: 255, maxMessage: 'L’adresse e-mail ne peut pas dépasser {{ limit }} caractères.'),
                ],
            ])
            ->add('recipientName', TextType::class, [
                'label' => 'Nom du destinataire',
                'required' => false,
                'constraints' => [
                    new Assert\Length(max: 100, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.'),
                    new Assert\Regex(
                        pattern: '/[\r\n\x00-\x1F\x7F]/',
                        match: false,
                        message: 'Le nom ne peut pas contenir de caractères de contrôle.'
                    ),
                ],
            ])
            ->add('subject', TextType::class, [
                'label' => 'Sujet',
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez saisir un sujet.'),
                    new Assert\Length(max: 255, maxMessage: 'Le sujet ne peut pas dépasser {{ limit }} caractères.'),
                    new Assert\Regex(
                        pattern: '/[\r\n\x00-\x1F\x7F]/',
                        match: false,
                        message: 'Le sujet ne peut pas contenir de caractères de contrôle.'
                    ),
                ],
            ])
            ->add('body', TextareaType::class, [
                'label' => 'Message',
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez saisir le contenu du message.'),
                    new Assert\Length(max: 50000, maxMessage: 'Le message ne peut pas dépasser {{ limit }} caractères.'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
