<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'attr' => [
                    'placeholder' => 'exemple@domaine.com',
                    'autocomplete' => 'email',
                ],
                'constraints' => [
                    new NotBlank(message: 'L\'adresse e-mail est obligatoire.'),
                    new Email(message: 'Veuillez saisir une adresse e-mail valide.'),
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôles / Droits d\'accès',
                'choices' => [
                    'Administrateur' => 'ROLE_ADMIN',
                    'Utilisateur standard' => 'ROLE_USER',
                ],
                'multiple' => true,
                'expanded' => true,
                'help' => 'Le rôle ROLE_USER est attribué automatiquement par sécurité.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'user_edit_token',
        ]);
    }
}
