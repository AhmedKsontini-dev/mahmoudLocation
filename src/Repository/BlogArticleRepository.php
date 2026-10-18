<?php

namespace App\Repository;

use App\Entity\BlogArticle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BlogArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogArticle::class);
    }

    public function findPublishedByLocale(string $locale = 'fr'): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.translations', 't')
            ->andWhere('a.status = :status')
            ->andWhere('t.locale = :locale OR t.locale = :fallback')
            ->setParameter('status', 'published')
            ->setParameter('locale', $locale)
            ->setParameter('fallback', 'fr')
            ->groupBy('a.id')
            ->orderBy('a.publishedAt', 'DESC')
            ->addOrderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOnePublishedBySlug(string $slug, string $locale = 'fr'): ?BlogArticle
    {
        $articles = $this->createQueryBuilder('a')
            ->leftJoin('a.translations', 't')
            ->andWhere('a.slug = :slug')
            ->andWhere('a.status = :status')
            ->andWhere('t.locale = :locale OR t.locale = :fallback')
            ->setParameter('slug', $slug)
            ->setParameter('status', 'published')
            ->setParameter('locale', $locale)
            ->setParameter('fallback', 'fr')
            ->getQuery()
            ->getResult();

        return $articles[0] ?? null;
    }
}
