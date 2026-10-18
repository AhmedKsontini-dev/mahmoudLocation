<?php

namespace App\Tests\Entity;

use App\Entity\BlogArticle;
use App\Entity\BlogArticleTranslation;
use App\Form\BlogArticleTranslationType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Forms;

class BlogArticleTest extends TestCase
{
    public function testSanitizeHtmlRemovesDangerousContent(): void
    {
        $rawContent = '<p>Bonjour <script>alert(1)</script> <a href="javascript:alert(1)">lien</a> <strong>important</strong></p>';

        $sanitized = BlogArticle::sanitizeHtml($rawContent);

        $this->assertStringNotContainsString('<script', $sanitized);
        $this->assertStringNotContainsString('javascript:', $sanitized);
        $this->assertStringContainsString('<strong>important</strong>', $sanitized);
    }

    public function testTranslationFieldsAreNotAllRequired(): void
    {
        $formFactory = Forms::createFormFactory();
        $form = $formFactory->create(BlogArticleTranslationType::class, new BlogArticleTranslation());

        $this->assertFalse($form->get('title')->getConfig()->getRequired());
        $this->assertFalse($form->get('slug')->getConfig()->getRequired());
        $this->assertFalse($form->get('content')->getConfig()->getRequired());
    }
}
