<?php

namespace App\Tests;

use App\Entity\BlogCategoryTranslation;
use App\Form\BlogCategoryTranslationType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BlogCategoryTranslationFormTest extends KernelTestCase
{
    public function testOnlyFrenchCategoryNameIsRequired(): void
    {
        self::bootKernel();
        $formFactory = static::getContainer()->get('form.factory');

        $frenchTranslation = new BlogCategoryTranslation();
        $frenchTranslation->setLocale('fr');
        $frenchForm = $formFactory->create(BlogCategoryTranslationType::class, $frenchTranslation, ['csrf_protection' => false]);

        $englishTranslation = new BlogCategoryTranslation();
        $englishTranslation->setLocale('en');
        $englishForm = $formFactory->create(BlogCategoryTranslationType::class, $englishTranslation, ['csrf_protection' => false]);

        self::assertTrue($frenchForm->get('name')->getConfig()->getRequired());
        self::assertFalse($englishForm->get('name')->getConfig()->getRequired());

        $frenchForm->submit(['name' => '']);
        $englishForm->submit(['name' => '']);

        self::assertFalse($frenchForm->isValid());
        self::assertTrue(
            $englishForm->isValid(),
            implode('; ', array_map(
                static fn ($error): string => $error->getMessage(),
                iterator_to_array($englishForm->getErrors(true)),
            )),
        );
    }
}
