<?php

namespace App\Tests;

use App\Entity\BlogCategory;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BlogCategoryCreationTest extends WebTestCase
{
    public function testCategoryIsCreatedAndVisibleAfterSubmission(): void
    {
        $client = static::createClient();
        $suffix = bin2hex(random_bytes(4));
        $name = 'Catégorie de test ' . $suffix;
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $user = (new User())
            ->setEmail('blog-category-' . $suffix . '@example.test')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('not-used');
        $entityManager->persist($user);
        $entityManager->flush();
        $client->loginUser($user);

        try {
            $crawler = $client->request('GET', '/admin/blog/categories/new');
            self::assertResponseIsSuccessful();

            $submitForm = $crawler->selectButton('💾 Enregistrer')->form();
            $frenchName = $crawler->filter('.translation-pane[data-locale="fr"] input')->first()->attr('name');
            $submitForm->get((string) $frenchName)->setValue($name);

            $client->submit($submitForm);
            self::assertResponseRedirects('/admin/blog/categories');

            $crawler = $client->followRedirect();
            self::assertResponseIsSuccessful();
            self::assertStringContainsString($name, (string) $crawler->html());
            self::assertStringContainsString(
                'La catégorie du blog a été créée avec succès.',
                (string) $crawler->html(),
            );

            $entityManager = static::getContainer()->get('doctrine')->getManager();
            $category = $entityManager->getRepository(BlogCategory::class)->findOneBy([
                'slug' => 'categorie-de-test-' . $suffix,
            ]);
            self::assertInstanceOf(BlogCategory::class, $category);
            self::assertSame($name, $category->getNameTranslated('fr'));
        } finally {
            $entityManager = static::getContainer()->get('doctrine')->getManager();
            $category = $entityManager->getRepository(BlogCategory::class)->findOneBy([
                'slug' => 'categorie-de-test-' . $suffix,
            ]);
            if ($category !== null) {
                $entityManager->remove($category);
            }

            $testUser = $entityManager->getRepository(User::class)->findOneBy([
                'email' => 'blog-category-' . $suffix . '@example.test',
            ]);
            if ($testUser !== null) {
                $entityManager->remove($testUser);
            }

            $entityManager->flush();
        }
    }

    public function testMissingFrenchNameShowsFieldErrorAndDoesNotPersist(): void
    {
        $client = static::createClient();
        $suffix = bin2hex(random_bytes(4));
        $user = (new User())
            ->setEmail('blog-category-invalid-' . $suffix . '@example.test')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('not-used');
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
        $client->loginUser($user);
        $categoryCountBefore = $entityManager->getRepository(BlogCategory::class)->count([]);

        try {
            $crawler = $client->request('GET', '/admin/blog/categories/new');
            $submitForm = $crawler->selectButton('💾 Enregistrer')->form();
            $client->submit($submitForm);

            self::assertResponseStatusCodeSame(422);
            self::assertStringContainsString(
                'Le nom de la catégorie en français est obligatoire.',
                (string) $client->getResponse()->getContent(),
            );

            $entityManager = static::getContainer()->get('doctrine')->getManager();
            self::assertSame($categoryCountBefore, $entityManager->getRepository(BlogCategory::class)->count([]));
        } finally {
            $entityManager = static::getContainer()->get('doctrine')->getManager();
            $testUser = $entityManager->getRepository(User::class)->findOneBy([
                'email' => 'blog-category-invalid-' . $suffix . '@example.test',
            ]);
            if ($testUser !== null) {
                $entityManager->remove($testUser);
                $entityManager->flush();
            }
        }
    }
}
