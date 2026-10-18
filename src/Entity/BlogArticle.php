<?php

namespace App\Entity;

use App\Config\LocaleConfig;
use App\Repository\BlogArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: BlogArticleRepository::class)]
#[ORM\Table(name: 'blog_article')]
#[ORM\UniqueConstraint(name: 'UNIQ_BLOG_ARTICLE_SLUG', columns: ['slug'])]
#[UniqueEntity(fields: ['slug'], message: 'Cet article de blog possède déjà ce slug.')]
class BlogArticle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageAlt = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $authorName = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $readTime = 5;

    #[ORM\Column(length: 20)]
    private string $status = 'draft';

    #[ORM\Column(name: 'is_featured')]
    private bool $isFeatured = false;

    #[ORM\Column(name: 'published_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $publishedAt = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    /** @var Collection<int, BlogArticleTranslation> */
    #[ORM\OneToMany(mappedBy: 'article', targetEntity: BlogArticleTranslation::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $translations;

    /** @var Collection<int, BlogCategory> */
    #[ORM\ManyToMany(targetEntity: BlogCategory::class, inversedBy: 'articles')]
    #[ORM\JoinTable(name: 'blog_article_category')]
    private Collection $categories;

    /** @var Collection<int, BlogArticleImage> */
    #[ORM\OneToMany(mappedBy: 'article', targetEntity: BlogArticleImage::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $galleryImages;

    /** @var Collection<int, BlogArticleVideo> */
    #[ORM\OneToMany(mappedBy: 'article', targetEntity: BlogArticleVideo::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $videos;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->galleryImages = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getImageAlt(): ?string
    {
        return $this->imageAlt;
    }

    public function setImageAlt(?string $imageAlt): static
    {
        $this->imageAlt = $imageAlt;
        return $this;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setAuthorName(?string $authorName): static
    {
        $this->authorName = $authorName;
        return $this;
    }

    public function getReadTime(): int
    {
        return $this->readTime;
    }

    public function setReadTime(int $readTime): static
    {
        $this->readTime = $readTime;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function isFeatured(): bool
    {
        return $this->isFeatured;
    }

    public function setIsFeatured(bool $isFeatured): static
    {
        $this->isFeatured = $isFeatured;
        return $this;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): static
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /** @return Collection<int, BlogArticleTranslation> */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(BlogArticleTranslation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setArticle($this);
        }

        return $this;
    }

    public function removeTranslation(BlogArticleTranslation $translation): static
    {
        if ($this->translations->removeElement($translation) && $translation->getArticle() === $this) {
            $translation->setArticle(null);
        }

        return $this;
    }

    public function getTranslation(string $locale): ?BlogArticleTranslation
    {
        foreach ($this->translations as $translation) {
            if ($translation->getLocale() === $locale) {
                return $translation;
            }
        }

        if ($locale !== 'fr') {
            foreach ($this->translations as $translation) {
                if ($translation->getLocale() === 'fr') {
                    return $translation;
                }
            }
        }

        return $this->translations->first() ?: null;
    }

    public function getTitleTranslated(string $locale): ?string
    {
        return $this->getTranslation($locale)?->getTitle() ?? $this->getTranslation('fr')?->getTitle();
    }

    public function getExcerptTranslated(string $locale): ?string
    {
        return $this->getTranslation($locale)?->getExcerpt() ?? $this->getTranslation('fr')?->getExcerpt();
    }

    public function getContentTranslated(string $locale): ?string
    {
        return $this->getTranslation($locale)?->getContent() ?? $this->getTranslation('fr')?->getContent();
    }

    /** @return Collection<int, BlogCategory> */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(BlogCategory $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(BlogCategory $category): static
    {
        $this->categories->removeElement($category);
        return $this;
    }

    /** @return Collection<int, BlogArticleImage> */
    public function getGalleryImages(): Collection
    {
        return $this->galleryImages;
    }

    public function addGalleryImage(BlogArticleImage $image): static
    {
        if (!$this->galleryImages->contains($image)) {
            $this->galleryImages->add($image);
            $image->setArticle($this);
        }

        return $this;
    }

    public function removeGalleryImage(BlogArticleImage $image): static
    {
        if ($this->galleryImages->removeElement($image) && $image->getArticle() === $this) {
            $image->setArticle(null);
        }

        return $this;
    }

    /** @return Collection<int, BlogArticleVideo> */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(BlogArticleVideo $video): static
    {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
            $video->setArticle($this);
        }

        return $this;
    }

    public function removeVideo(BlogArticleVideo $video): static
    {
        if ($this->videos->removeElement($video) && $video->getArticle() === $this) {
            $video->setArticle(null);
        }

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function getSafeContentTranslated(string $locale): ?string
    {
        $content = $this->getContentTranslated($locale);
        if ($content === null || trim($content) === '') {
            return null;
        }

        return self::sanitizeHtml($content);
    }

    public static function sanitizeHtml(string $content): string
    {
        if (trim($content) === '') {
            return '';
        }

        $allowedTags = [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li', 'a', 'blockquote', 'span', 'img', 'figure', 'figcaption', 'div', 'iframe',
        ];

        $safeContent = preg_replace('/<\s*(script|style|object|embed|svg|math)[^>]*>.*?<\/\s*\1\s*>/is', '', $content);
        $safeContent = $safeContent ?? $content;

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $safeContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();

        $elements = iterator_to_array($dom->getElementsByTagName('*'));
        foreach (array_reverse($elements) as $element) {
            $tagName = strtolower($element->tagName);

            if (!in_array($tagName, $allowedTags, true)) {
                $parent = $element->parentNode;
                if ($parent !== null) {
                    $fragment = $dom->createDocumentFragment();
                    while ($element->firstChild !== null) {
                        $fragment->appendChild($element->firstChild);
                    }
                    $parent->replaceChild($fragment, $element);
                }

                continue;
            }

            if ($element->attributes === null) {
                continue;
            }

            for ($index = $element->attributes->length - 1; $index >= 0; --$index) {
                $attribute = $element->attributes->item($index);
                if ($attribute === null) {
                    continue;
                }

                $attributeName = strtolower($attribute->nodeName);

                if (str_starts_with($attributeName, 'on')) {
                    $element->removeAttribute($attributeName);
                    continue;
                }

                if (!in_array($attributeName, ['href', 'src', 'srcset', 'alt', 'title'], true)) {
                    continue;
                }

                $value = trim((string) $attribute->nodeValue);
                if ($value === '') {
                    $element->removeAttribute($attributeName);
                    continue;
                }

                if (preg_match('/^(?:javascript:|data:)/i', $value) === 1) {
                    $element->removeAttribute($attributeName);
                    continue;
                }

                if ($attributeName === 'href' && !preg_match('/^(?:[a-z]+:\/\/|mailto:|tel:|#|\/|\.\.?\/)/i', $value)) {
                    $element->removeAttribute($attributeName);
                }
            }
        }

        $html = $dom->saveHTML();
        if ($html === false) {
            return '';
        }

        $html = preg_replace('/^<\?xml[^>]*>\s*/i', '', $html);
        $html = preg_replace('/^<!DOCTYPE.*?<html><body>|<\/body><\/html>$/is', '', $html);

        return trim((string) $html);
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
