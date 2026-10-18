<?php

namespace App\Tests;

use App\Entity\BlogArticle;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BlogArticleFormErrorsTest extends WebTestCase
{
    public function testEmptySubmissionShowsFrenchFieldErrorsAndPreservesEnteredValues(): void
    {
        $client = static::createClient();
        $suffix = bin2hex(random_bytes(4));
        $user = $this->createAdmin('blog-article-empty-' . $suffix . '@example.test');
        $client->loginUser($user);

        try {
            $crawler = $client->request('GET', '/admin/blog/articles/new');
            self::assertResponseIsSuccessful();
            $form = $crawler->selectButton('💾 Enregistrer')->form();
            $titleField = (string) $crawler->filter('.translation-pane[data-locale="fr"] input[name$="[title]"]')->attr('name');

            $client->submit($form);
            self::assertResponseStatusCodeSame(422);
            $html = (string) $client->getResponse()->getContent();
            self::assertStringContainsString('Le titre en français est obligatoire.', $html);
            self::assertStringContainsString('Le contenu en français est obligatoire.', $html);
            self::assertStringContainsString('Impossible d’enregistrer l’article.', $html);
            self::assertStringContainsString('data-tab="translations"', $html);
            self::assertStringContainsString('class="tab-btn active"', $html);

            $crawler = $client->getCrawler();
            $form = $crawler->selectButton('💾 Enregistrer')->form();
            $form->get($titleField)->setValue('Titre conservé ' . $suffix);
            $client->submit($form);

            self::assertResponseStatusCodeSame(422);
            self::assertStringContainsString('Titre conservé ' . $suffix, (string) $client->getResponse()->getContent());
            self::assertStringContainsString('Le contenu en français est obligatoire.', (string) $client->getResponse()->getContent());

            $crawler = $client->request('GET', '/admin/blog/articles/new');
            $form = $this->fillFrenchArticle($crawler, 'Article anglais incomplet ' . $suffix);
            $englishTitleField = (string) $crawler->filter('.translation-pane[data-locale="en"] input[name$="[title]"]')->attr('name');
            $form->get($englishTitleField)->setValue('English title ' . $suffix);
            $client->submit($form);

            self::assertResponseStatusCodeSame(422);
            $html = (string) $client->getResponse()->getContent();
            self::assertStringContainsString('Le contenu en anglais est obligatoire lorsque le titre est renseigné.', $html);
            self::assertStringContainsString('data-error-locale="en"', $html);
        } finally {
            $this->removeAdmin($user);
        }
    }

    public function testInvalidMainImageShowsItsFieldError(): void
    {
        $client = static::createClient();
        $suffix = bin2hex(random_bytes(4));
        $user = $this->createAdmin('blog-article-image-' . $suffix . '@example.test');
        $client->loginUser($user);
        $tmpFile = tempnam(sys_get_temp_dir(), 'blog-image-');
        file_put_contents($tmpFile, 'not an image');

        try {
            $crawler = $client->request('GET', '/admin/blog/articles/new');
            $form = $crawler->selectButton('💾 Enregistrer')->form();
            $imageFieldName = (string) $crawler->filter('input[type="file"][name$="[mainImageFile]"]')->attr('name');
            $form->get($imageFieldName)->upload($tmpFile);
            $client->submit($form);

            self::assertResponseStatusCodeSame(422);
            $html = (string) $client->getResponse()->getContent();
            self::assertStringContainsString('Veuillez sélectionner une image JPG, JPEG, PNG ou WebP.', $html);
            self::assertStringContainsString('Image principale', $html);
        } finally {
            if (is_file($tmpFile)) {
                unlink($tmpFile);
            }
            $this->removeAdmin($user);
        }
    }

    public function testValidFrenchArticleIsPersistedAndDuplicateSlugIsReported(): void
    {
        $client = static::createClient();
        $suffix = bin2hex(random_bytes(4));
        $user = $this->createAdmin('blog-article-valid-' . $suffix . '@example.test');
        $client->loginUser($user);
        $title = 'Article test ' . $suffix;
        $slug = 'article-test-' . $suffix;

        try {
            $crawler = $client->request('GET', '/admin/blog/articles/new');
            $form = $this->fillFrenchArticle($crawler, $title);
            $client->submit($form);

            self::assertResponseRedirects('/admin/blog/articles');
            $crawler = $client->followRedirect();
            self::assertResponseIsSuccessful();
            self::assertStringContainsString($title, (string) $crawler->html());

            $entityManager = static::getContainer()->get('doctrine')->getManager();
            $article = $entityManager->getRepository(BlogArticle::class)->findOneBy(['slug' => $slug]);
            self::assertInstanceOf(BlogArticle::class, $article);
            self::assertSame('<p>Contenu de test en plusieurs paragraphes.</p>', $article->getContentTranslated('fr'));

            $crawler = $client->request('GET', '/admin/blog/articles/' . $article->getId() . '/edit');
            $editForm = $crawler->selectButton('💾 Enregistrer')->form();
            $frenchTitleField = (string) $crawler->filter('.translation-pane[data-locale="fr"] input[name$="[title]"]')->attr('name');
            $editForm->get($frenchTitleField)->setValue('Titre modifié ' . $suffix);
            $client->submit($editForm);

            self::assertResponseRedirects('/admin/blog/articles');
            $article = $entityManager->getRepository(BlogArticle::class)->findOneBy(['slug' => $slug]);
            self::assertSame('Titre modifié ' . $suffix, $article->getTitleTranslated('fr'));

            $crawler = $client->request('GET', '/admin/blog/articles/new');
            $duplicateForm = $this->fillFrenchArticle($crawler, $title);
            $client->submit($duplicateForm);

            self::assertResponseStatusCodeSame(422);
            self::assertStringContainsString('Ce slug existe déjà. Choisissez un autre slug.', (string) $client->getResponse()->getContent());
            self::assertSame(1, $entityManager->getRepository(BlogArticle::class)->count(['slug' => $slug]));
        } finally {
            $entityManager = static::getContainer()->get('doctrine')->getManager();
            $article = $entityManager->getRepository(BlogArticle::class)->findOneBy(['slug' => $slug]);
            if ($article !== null) {
                $entityManager->remove($article);
                $entityManager->flush();
            }
            $this->removeAdmin($user);
        }
    }

    private function fillFrenchArticle(\Symfony\Component\DomCrawler\Crawler $crawler, string $title): \Symfony\Component\DomCrawler\Form
    {
        $form = $crawler->selectButton('💾 Enregistrer')->form();
        $form->get((string) $crawler->filter('.translation-pane[data-locale="fr"] input[name$="[title]"]')->attr('name'))->setValue($title);
        $form->get((string) $crawler->filter('.translation-pane[data-locale="fr"] textarea[name$="[content]"]')->attr('name'))->setValue('<p>Contenu de test en plusieurs paragraphes.</p>');
        $form->get((string) $crawler->filter('input[name$="[readTime]"]')->attr('name'))->setValue('5');

        return $form;
    }

    private function createAdmin(string $email): User
    {
        $user = (new User())
            ->setEmail($email)
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('not-used');
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    private function removeAdmin(User $user): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $managedUser = $entityManager->getRepository(User::class)->find($user->getId());
        if ($managedUser !== null) {
            $entityManager->remove($managedUser);
            $entityManager->flush();
        }
    }
}
