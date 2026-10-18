<?php

namespace App\Tests;

use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Entity\Destination;
use App\Entity\Excursion;
use App\Entity\ExcursionTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Filtre « Catégories » de la liste frontend des excursions (/fr/excursions) :
 * liste radio identique aux destinations avec compteur d'excursions publiées,
 * filtrage réel en base combiné aux autres filtres, pagination recalculée,
 * règles de visibilité (active/published uniquement) et traductions.
 */
class FrontendExcursionCategoryFilterTest extends WebTestCase
{
    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * Cat A (icône 🏖️, 3 excursions actives → 2 pages) ;
     * Cat B (sans icône, 1 excursion active) ;
     * Cat C (active mais seule une excursion draft → absente du menu, vide côté résultats).
     *
     * @return array<string, mixed>
     */
    private function createFixtures(EntityManagerInterface $em): array
    {
        $suffix = bin2hex(random_bytes(4));

        $names = [
            'frA' => 'Plages QA '.$suffix,
            'enA' => 'Beaches QA '.$suffix,
            'frB' => 'Aventure QA '.$suffix,
            'frC' => 'Dormante QA '.$suffix,
        ];

        $catA = (new Category())->setSlug('filter-cat-a-'.$suffix)->setIcon('🏖️')->setPosition(1)->setIsActive(true);
        $catA->addTranslation((new CategoryTranslation())->setLocale('fr')->setName($names['frA']));
        $catA->addTranslation((new CategoryTranslation())->setLocale('en')->setName($names['enA']));

        $catB = (new Category())->setSlug('filter-cat-b-'.$suffix)->setPosition(2)->setIsActive(true);
        $catB->addTranslation((new CategoryTranslation())->setLocale('fr')->setName($names['frB']));

        $catC = (new Category())->setSlug('filter-cat-c-'.$suffix)->setPosition(3)->setIsActive(true);
        $catC->addTranslation((new CategoryTranslation())->setLocale('fr')->setName($names['frC']));

        $dest1 = (new Destination())->setSlug('filter-dest-1-'.$suffix);
        $dest2 = (new Destination())->setSlug('filter-dest-2-'.$suffix);

        $titles = [];
        $excursions = [];
        $definitions = [
            ['a1', $catA, $dest1, '100.00', Excursion::STATUS_ACTIVE],
            ['a2', $catA, $dest2, '40.00', Excursion::STATUS_ACTIVE],
            ['a3', $catA, $dest1, '80.00', Excursion::STATUS_DRAFT],
            ['a4', $catA, $dest1, '70.00', Excursion::STATUS_ACTIVE],
            ['b1', $catB, $dest1, '60.00', Excursion::STATUS_ACTIVE],
            ['c1', $catC, $dest1, '90.00', Excursion::STATUS_DRAFT],
        ];

        foreach ($definitions as [$key, $category, $destination, $price, $status]) {
            $title = 'Excursion QA '.$key.' '.$suffix;
            $titles[$key] = $title;

            $excursion = (new Excursion())
                ->setCategory($category)
                ->setDestination($destination)
                ->setPriceAdult($price)
                ->setSlug('filter-exc-'.$key.'-'.$suffix)
                ->setStatus($status);
            $excursion->addTranslation((new ExcursionTranslation())->setLocale('fr')->setTitle($title)->setDescription('<p>Test</p>'));
            $excursion->addTranslation((new ExcursionTranslation())->setLocale('en')->setTitle($title)->setDescription('<p>Test</p>'));

            $em->persist($excursion);
            $excursions[] = $excursion;
        }

        $em->persist($catA);
        $em->persist($catB);
        $em->persist($catC);
        $em->persist($dest1);
        $em->persist($dest2);
        $em->flush();

        return [
            'suffix' => $suffix,
            'catA' => $catA->getId(),
            'catB' => $catB->getId(),
            'catC' => $catC->getId(),
            'dest1' => $dest1->getId(),
            'dest2' => $dest2->getId(),
            'names' => $names,
            'titles' => $titles,
            'slugs' => ['a1' => 'filter-exc-a1-'.$suffix],
        ];
    }

    private function removeFixtures(EntityManagerInterface $em, array $fixture): void
    {
        $em->clear();

        $excursions = $em->createQueryBuilder('e')
            ->select('e')->from(Excursion::class, 'e')
            ->where('e.slug LIKE :pattern')
            ->setParameter('pattern', 'filter-exc-%'.$fixture['suffix'])
            ->getQuery()->getResult();
        foreach ($excursions as $excursion) {
            $em->remove($excursion);
        }

        foreach (['catA', 'catB', 'catC'] as $key) {
            $category = $em->getRepository(Category::class)->find($fixture[$key]);
            if (null !== $category) {
                $em->remove($category);
            }
        }
        foreach (['dest1', 'dest2'] as $key) {
            $destination = $em->getRepository(Destination::class)->find($fixture[$key]);
            if (null !== $destination) {
                $em->remove($destination);
            }
        }

        $em->flush();
        $em->clear();
    }

    private function radioChecked(string $html, string $value): bool
    {
        return preg_match('/<input type="radio" name="category" value="'.preg_quote($value, '/').'"\s*checked/s', $html) === 1;
    }

    private function categoryBadgeCount(string $html, int $categoryId): ?int
    {
        preg_match('/name="category" value="'.preg_quote((string) $categoryId, '/').'".*?filter-item__count">(\d+)</s', $html, $matches);

        return isset($matches[1]) ? (int) $matches[1] : null;
    }

    public function testCategoriesAreListedLikeDestinationsWithPublishedCounts(): void
    {
        $client = static::createClient();
        $em = $this->entityManager();
        $fixture = $this->createFixtures($em);

        try {
            $crawler = $client->request('GET', '/fr/excursions');
            self::assertResponseIsSuccessful();
            $html = $client->getResponse()->getContent();

            // Plus aucun select pour les catégories : liste radio identique aux destinations.
            self::assertSame(0, $crawler->filter('select[name="category"]')->count(), 'Le composant select doit être supprimé.');
            self::assertGreaterThan(0, $crawler->filter('#filter-form .filter-list input[type="radio"][name="category"]')->count(),
                'Les catégories doivent être rendues comme les destinations (radios dans filter-list).');

            // Option « Toutes les catégories » cochée par défaut.
            self::assertStringContainsString('Toutes les catégories', $html);
            self::assertTrue($this->radioChecked($html, ''), 'L’option par défaut doit être cochée au premier chargement.');

            // Catégorie A : icône + nom traduits, compteur = 3 excursions publiées (draft exclu).
            self::assertStringContainsString('🏖️', $html);
            self::assertStringContainsString($fixture['names']['frA'], $html);
            self::assertSame(3, $this->categoryBadgeCount($html, $fixture['catA']),
                'Le compteur doit ignorer la draft et compter les excursions réellement publiées.');

            // Catégorie B sans icône : proposée avec son compteur propre.
            self::assertStringContainsString($fixture['names']['frB'], $html);
            self::assertSame(1, $this->categoryBadgeCount($html, $fixture['catB']));

            // Catégorie C : aucune excursion publiée → ne doit pas être proposée du tout.
            self::assertStringNotContainsString($fixture['names']['frC'], $html, 'Une catégorie sans excursion publiée ne doit pas apparaître.');

            // Règles de visibilité : les drafts ne sont jamais listées.
            self::assertStringNotContainsString($fixture['titles']['a3'], $html);
            self::assertStringNotContainsString($fixture['titles']['c1'], $html);

            // Le formulaire de filtres repart bien de la page 1 (champ caché existant).
            self::assertSame('1', $crawler->filter('#filter-form input[name="page"]')->attr('value'));

            // Le lien « Réinitialiser » pointe vers la page sans aucun paramètre de filtre.
            $resetHref = $crawler->filter('.filter-panel__reset')->attr('href');
            self::assertStringNotContainsString('category', (string) $resetHref, 'Réinitialiser doit aussi effacer la catégorie.');
        } finally {
            $this->removeFixtures($em, $fixture);
        }
    }

    public function testFilterByCategoryOnlyShowsMatchingPublishedExcursions(): void
    {
        $client = static::createClient();
        $em = $this->entityManager();
        $fixture = $this->createFixtures($em);

        try {
            $crawler = $client->request('GET', '/fr/excursions?category='.$fixture['catA']);
            self::assertResponseIsSuccessful();
            $html = $client->getResponse()->getContent();

            // Les 3 excursions actives de A sont comptées ; page 1 (limite 2, id DESC) : a4 puis a2.
            self::assertStringContainsString('3 excursions', $html);
            foreach (['a2', 'a4'] as $key) {
                self::assertStringContainsString($fixture['titles'][$key], $html);
            }
            self::assertStringNotContainsString($fixture['titles']['b1'], $html, 'Une excursion d’une autre catégorie ne doit pas apparaître.');
            self::assertStringNotContainsString($fixture['titles']['a3'], $html, 'Une excursion draft ne doit jamais apparaître.');

            // La sélection est conservée après le rechargement des résultats (radio cochée).
            self::assertTrue($this->radioChecked($html, (string) $fixture['catA']), 'La catégorie choisie doit rester cochée.');

            // 2 par page : la page 1 affiche exactement 2 cartes.
            self::assertCount(2, $crawler->filter('.excursion-card'));
        } finally {
            $this->removeFixtures($em, $fixture);
        }
    }

    public function testCategoryCombinesWithDestinationPriceAndSearch(): void
    {
        $client = static::createClient();
        $em = $this->entityManager();
        $fixture = $this->createFixtures($em);

        try {
            $catA = $fixture['catA'];

            // Catégorie + destination : uniquement les excursions de A sur dest1 (a1, a4).
            $client->request('GET', '/fr/excursions?category='.$catA.'&destination='.$fixture['dest1']);
            self::assertResponseIsSuccessful();
            $html = $client->getResponse()->getContent();
            self::assertStringContainsString($fixture['titles']['a1'], $html);
            self::assertStringContainsString($fixture['titles']['a4'], $html);
            self::assertStringNotContainsString($fixture['titles']['a2'], $html, 'Le filtre destination doit rester combiné à la catégorie.');

            // Catégorie + prix maximum : seule a2 (40 DT) reste dans A.
            $client->request('GET', '/fr/excursions?category='.$catA.'&max_price=50');
            $html = $client->getResponse()->getContent();
            self::assertStringContainsString($fixture['titles']['a2'], $html);
            self::assertStringNotContainsString($fixture['titles']['a1'], $html);
            self::assertStringNotContainsString($fixture['titles']['a4'], $html);

            // Catégorie + sélection d’une excursion précise (recherche par slug).
            $client->request('GET', '/fr/excursions?category='.$catA.'&excursion='.$fixture['slugs']['a1']);
            $html = $client->getResponse()->getContent();
            self::assertStringContainsString($fixture['titles']['a1'], $html);
            self::assertStringNotContainsString($fixture['titles']['a2'], $html);
            self::assertStringNotContainsString($fixture['titles']['a4'], $html);

            // Combinaison impossible : message clair, pas d’erreur.
            $client->request('GET', '/fr/excursions?category='.$catA.'&destination='.$fixture['dest2'].'&max_price=10');
            self::assertResponseIsSuccessful();
            $html = $client->getResponse()->getContent();
            self::assertStringContainsString('Aucune excursion publiée ne correspond à la catégorie sélectionnée.', $html);
        } finally {
            $this->removeFixtures($em, $fixture);
        }
    }

    public function testPaginationIsRecalculatedAndKeepsTheCategory(): void
    {
        $client = static::createClient();
        $em = $this->entityManager();
        $fixture = $this->createFixtures($em);

        try {
            $catA = $fixture['catA'];

            // Page 1 : 2 cartes (limite existante), lien de pagination avec la catégorie conservée.
            $crawler = $client->request('GET', '/fr/excursions?category='.$catA);
            self::assertResponseIsSuccessful();
            $html = $client->getResponse()->getContent();
            self::assertCount(2, $crawler->filter('.excursion-card'));

            preg_match_all('/href="([^"]*excursions\?[^"]*)"/', $html, $links);
            $pageTwoLink = array_filter($links[1], function (string $href) use ($catA) {
                return str_contains(html_entity_decode($href), 'category='.$catA) && str_contains(html_entity_decode($href), 'page=2');
            });
            self::assertNotEmpty($pageTwoLink, 'Les liens de pagination doivent conserver la catégorie filtrée.');

            // Page 2 : la 3e excursion active de A, total toujours de 3.
            $crawler = $client->request('GET', '/fr/excursions?category='.$catA.'&page=2');
            self::assertResponseIsSuccessful();
            $html = $client->getResponse()->getContent();
            self::assertCount(1, $crawler->filter('.excursion-card'));
            self::assertStringContainsString($fixture['titles']['a1'], $html, 'La page 2 doit montrer l’excursion restante de la catégorie.');
            self::assertStringContainsString('3 excursions', $html);
        } finally {
            $this->removeFixtures($em, $fixture);
        }
    }

    public function testCategoryWithoutPublishedExcursionsShowsMessageNotError(): void
    {
        $client = static::createClient();
        $em = $this->entityManager();
        $fixture = $this->createFixtures($em);

        try {
            // Accès direct sur une catégorie sans excursion publiée (cat C, draft uniquement).
            $client->request('GET', '/fr/excursions?category='.$fixture['catC']);
            self::assertResponseIsSuccessful();
            $html = $client->getResponse()->getContent();

            self::assertStringContainsString('Aucune excursion publiée ne correspond à la catégorie sélectionnée.', $html);
            self::assertStringNotContainsString($fixture['titles']['c1'], $html, 'La draft de la catégorie ne doit pas être révélée.');
        } finally {
            $this->removeFixtures($em, $fixture);
        }
    }

    public function testFilterRespectsLocaleTranslations(): void
    {
        $client = static::createClient();
        $em = $this->entityManager();
        $fixture = $this->createFixtures($em);

        try {
            $crawler = $client->request('GET', '/en/excursions?category='.$fixture['catA']);
            self::assertResponseIsSuccessful();
            $html = $client->getResponse()->getContent();

            self::assertStringContainsString($fixture['names']['enA'], $html, 'Le libellé de la catégorie doit être traduit.');
            self::assertStringNotContainsString($fixture['names']['frA'], $html);
            self::assertTrue($this->radioChecked($html, (string) $fixture['catA']), 'La sélection est conservée après changement de langue.');
        } finally {
            $this->removeFixtures($em, $fixture);
        }
    }
}
