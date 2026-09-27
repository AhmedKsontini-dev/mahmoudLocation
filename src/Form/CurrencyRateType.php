<?php

namespace App\Form;

use App\Entity\CurrencyRate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class CurrencyRateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, ['disabled' => true])
            ->add('symbol', TextType::class)
            ->add('rate', NumberType::class, ['scale' => 6, 'html5' => true]);
    }
}