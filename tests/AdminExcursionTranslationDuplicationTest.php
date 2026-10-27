<?php

namespace App\Tests;

use App\Entity\Category;
use App\Entity\Destination;
use App\Entity\Excursion;
use App\Entity\User;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Empêche les duplications de traductions (erreur 1062 sur UNIQ_*_TRANSLATION_LOCALE)
 * lors de la création et de la modification d'une excursion : chaque collection
 * de traductions doit rester unique par (parent, locale), y compris quand des
 * entrées sont ajoutées ou supprimées via le formulaire.
 */
class AdminExcursionTranslationDuplicationTest extends WebTestCase
{
    private Connection $connection;

    private function createAdmin(string $suffix): array
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->connection = $entityManager->getConnection();

        $admin = (new User())
            ->setEmail('admin-excursion-dup-'.$suffix.'@example.test')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('not-used');

        $entityManager->persist($admin);
        $entityManager->flush();
        $client->loginUser($admin);

        return [$client, $entityManager, $admin];
    }

    private function createCategoryAndDestination(EntityManagerInterface $entityManager, string $suffix): array
    {
        $category = (new Category())->setSlug('dup-cat-'.$suffix);
        $destination = (new Destination())->setSlug('dup-des-'.$suffix);
        $entityManager->persist($category);
        $entityManager->persist($destination);
        $entityManager->flush();

        return [$category, $destination];
    }

    private function excursionFormToken($client, string $url): string
    {
        $crawler = $client->request('GET', $url);
        self::assertResponseIsSuccessful('Le formulaire excursion doit sérender.');
        $tokenNode = $crawler->filter('input[name="excursion[_token]"]')->first();
        self::assertSame(1, $tokenNode->count(), 'Le formulaire doit contenir un jeton CSRF.');

        return (string) $tokenNode->attr('value');
    }

    /**
     * Payload imitant exactement ce que poste le navigateur, champs cachés de
     * locale inclus (l'identifiant réel de chaque traduction).
     */
    private function fullPayload(string $category, string $destination, string $suffix): array
    {
        return [
            'category' => $category,
            'destination' => $destination,
            'type' => 'group',
            'status' => 'active',
            'durationHours' => '8',
            'durationMinutes' => '0',
            'startTime' => '',
            'endTime' => '',
            'priceAdult' => '99.00',
            'priceChild' => '49.00',
            'priceConverted' => '',
            'minParticipants' => '1',
            'maxParticipants' => '12',
            'cancellationHours' => '24',
            'slug' => 'dup-excursion-'.$suffix,
            'translations' => [
                ['locale' => 'fr', 'title' => 'Excursion '.$suffix, 'subtitle' => '', 'description' => 'Description française '.$suffix, 'location' => 'Tunis', 'metaTitle' => '', 'metaDescription' => ''],
                ['locale' => 'en', 'title' => 'Excursion '.$suffix.' EN', 'subtitle' => '', 'description' => 'English description', 'location' => 'Tunis', 'metaTitle' => '', 'metaDescription' => ''],
                ['locale' => 'it', 'title' => '', 'subtitle' => '', 'description' => '', 'location' => '', 'metaTitle' => '', 'metaDescription' => ''],
            ],
            'itineraries' => [
                [
                    'stepNumber' => '1', 'time' => '09:00', 'duration' => '30', 'position' => '0',
                    'translations' => [
                        ['locale' => 'fr', 'title' => 'Étape 1 FR', 'description' => ''],
                        ['locale' => 'en', 'title' => 'Step 1 EN', 'description' => ''],
                        ['locale' => 'it', 'title' => '', 'description' => ''],
                    ],
                ],
                [
                    'stepNumber' => '2', 'time' => '', 'duration' => '', 'position' => '1',
                    'translations' => [
                        ['locale' => 'fr', 'title' => 'Étape 2 FR', 'description' => ''],
                        ['locale' => 'en', 'title' => '', 'description' => ''],
                        ['locale' => 'it', 'title' => '', 'description' => ''],
                    ],
                ],
            ],
            'features' => [
                [
                    'icon' => '★', 'position' => '0',
                    'translations' => [
                        ['locale' => 'fr', 'title' => 'Feature A FR', 'subtitle' => ''],
                        ['locale' => 'en', 'title' => 'Feature A EN', 'subtitle' => ''],
                        ['locale' => 'it', 'title' => '', 'subtitle' => ''],
                    ],
                ],
                [
                    'icon' => '☂', 'position' => '1',
                    'translations' => [
                        ['locale' => 'fr', 'title' => 'Feature B FR', 'subtitle' => ''],
                        ['locale' => 'en', 'title' => '', 'subtitle' => ''],
                        ['locale' => 'it', 'title' => '', 'subtitle' => ''],
                    ],
                ],
            ],
            'inclusions' => [
                [
                    'type' => 'included', 'icon' => '✓', 'position' => '0',
                    'translations' => [
                        ['locale' => 'fr', 'text' => 'Inclus FR'],
                        ['locale' => 'en', 'text' => 'Included EN'],
                        ['locale' => 'it', 'text' => ''],
                    ],
                ],
                [
                    'type' => 'excluded', 'icon' => '✗', 'position' => '1',
                    'translations' => [
                        ['locale' => 'fr', 'text' => 'Exclus FR'],
                        ['locale' => 'en', 'text' => ''],
                        ['locale' => 'it', 'text' => ''],
                    ],
                ],
            ],
            'faqs' => [
                [
                    'position' => '0', 'isActive' => '1',
                    'translations' => [
                        ['locale' => 'fr', 'question' => 'Question FR ?', 'answer' => ''],
                        ['locale' => 'en', 'question' => 'Question EN?', 'answer' => ''],
                        ['locale' => 'it', 'question' => '', 'answer' => ''],
                    ],
                ],
            ],
            'badges' => [
                [
                    'badgeType' => 'best_seller', 'isActive' => '1',
                    'translations' => [
                        ['locale' => 'fr', 'text' => 'Best-seller FR'],
                        ['locale' => 'en', 'text' => 'Best-seller EN'],
                        ['locale' => 'it', 'text' => ''],
                    ],
                ],
            ],
        ];
    }

    private function postNew($client, array $payload, string $token): void
    {
        $client->request('POST', '/admin/excursions/new', ['excursion' => $payload + ['_token' => $token]]);
    }

    private function postEdit($client, int $excursionId, array $payload, string $token): void
    {
        $client->request('POST', '/admin/excursions/'.$excursionId.'/edit', ['excursion' => $payload + ['_token' => $token]]);
    }

    /**
     * Vérifie qu'aucun parent ne possède deux traductions pour la même locale,
     * en base (SQL, toutes les excursion_translation... incluses) comme en PHP.
     */
    private function assertNoDuplicateLocaleRowsInDatabase(): void
    {
        $tables = [
            'excursion_translation' => 'excursion_id',
            'excursion_itinerary_translation' => 'excursion_itinerary_id',
            'excursion_feature_translation' => 'excursion_feature_id',
            'excursion_inclusion_translation' => 'excursion_inclusion_id',
            'excursion_faq_translation' => 'excursion_faq_id',
            'excursion_badge_translation' => 'excursion_badge_id',
            'excursion_image_translation' => 'excursion_image_id',
        ];

        foreach ($tables as $table => $foreignKey) {
            $duplicates = $this->connection->fetchAllAssociative(
                'SELECT '.$foreignKey.' AS parent_id, locale, COUNT(*) AS c FROM '.$table
                .' GROUP BY '.$foreignKey.', locale HAVING c > 1'
            );
            self::assertSame([], $duplicates, 'Aucun doublon (parent, locale) en base dans '.$table.'.');
        }
    }

    private function assertOneTranslationPerLocale(Excursion $excursion): void
    {
        $parents = [
            'excursion' => [$excursion],
            'itinerary' => [],
            'feature' => [],
            'inclusion' => [],
            'faq' => [],
            'badge' => [],
        ];
        foreach ($excursion->getItineraries() as $p) { $parents['itinerary'][] = $p; }
        foreach ($excursion->getFeatures() as $p) { $parents['feature'][] = $p; }
        foreach ($excursion->getInclusions() as $p) { $parents['inclusion'][] = $p; }
        foreach ($excursion->getFaqs() as $p) { $parents['faq'][] = $p; }
        foreach ($excursion->getBadges() as $p) { $parents['badge'][] = $p; }

        foreach ($parents as $kind => $collection) {
            foreach ($collection as $parent) {
                $locales = array_map(static fn ($t) => $t->getLocale(), $parent->getTranslations()->toArray());
                self::assertSame(
                    array_unique($locales),
                    $locales,
                    'La collection de traductions ('.$kind.') ne contient jamais deux fois la même locale.'
                );
            }
        }
    }

    private function reloadExcursion(EntityManagerInterface $entityManager, int $id): Excursion
    {
        $entityManager->clear();
        $excursion = $entityManager->getRepository(Excursion::class)->find($id);
        self::assertInstanceOf(Excursion::class, $excursion);

        return $excursion;
    }

    public function testCreatingExcursionWithAllSectionsStoresOneTranslationPerLocale(): void
    {
        [$client, $entityManager, $admin] = $this->createAdmin($suffix = bin2hex(random_bytes(4)));
        [$category, $destination] = $this->createCategoryAndDestination($entityManager, $suffix);
        $excursionId = null;

        try {
            $token = $this->excursionFormToken($client, '/admin/excursions/new');
            $this->postNew($client, $this->fullPayload((string) $category->getId(), (string) $destination->getId(), $suffix), $token);

            self::assertResponseRedirects('/admin/excursions', null, 'La création doit réussir sans erreur 1062.');

            $excursion = $entityManager->getRepository(Excursion::class)->findOneBy(['slug' => 'dup-excursion-'.$suffix]);
            self::assertInstanceOf(Excursion::class, $excursion, 'L’excursion est créée.');
            $excursionId = $excursion->getId();

            self::assertCount(2, $excursion->getFeatures());
            self::assertCount(2, $excursion->getItineraries());
            self::assertCount(2, $excursion->getInclusions());
            self::assertCount(1, $excursion->getFaqs());
            self::assertCount(1, $excursion->getBadges());

            $this->assertOneTranslationPerLocale($excursion);

            // Les traductions vide (it / en vides) sont nettoyées, les autres conservées.
            $featureA = null;
            foreach ($excursion->getFeatures() as $feature) {
                if ('★' === $feature->getIcon()) { $featureA = $feature; }
            }
            self::assertNotNull($featureA);
            $featureALocales = array_map(static fn ($t) => $t->getLocale(), $featureA->getTranslations()->toArray());
            sort($featureALocales); // l'ordre de chargement Doctrine n'est pas garanti
            self::assertSame(['en', 'fr'], $featureALocales);
        } finally {
            $this->cleanup($entityManager, $admin, $excursionId, $category, $destination);
        }
    }

    public function testReSavingUnchangedExcursionCreatesNoDuplicateTranslations(): void
    {
        [$client, $entityManager, $admin] = $this->createAdmin($suffix = bin2hex(random_bytes(4)));
        [$category, $destination] = $this->createCategoryAndDestination($entityManager, $suffix);
        $excursionId = null;

        try {
            $token = $this->excursionFormToken($client, '/admin/excursions/new');
            $payload = $this->fullPayload((string) $category->getId(), (string) $destination->getId(), $suffix);
            $this->postNew($client, $payload, $token);
            self::assertResponseRedirects('/admin/excursions');

            $excursion = $entityManager->getRepository(Excursion::class)->findOneBy(['slug' => 'dup-excursion-'.$suffix]);
            $excursionId = $excursion->getId();
            $before = $this->translationRowCounts();

            // Test B : modification sans changement de collections.
            $editToken = $this->excursionFormToken($client, '/admin/excursions/'.$excursionId.'/edit');
            $this->postEdit($client, $excursionId, $payload, $editToken);
            self::assertResponseRedirects('/admin/excursions', null, 'La modification doit réussir sans doublon.');

            self::assertSame($before, $this->translationRowCounts(), 'Re-sauvergarde identique : aucun compteur de traductions ne bouge.');

            // Test C : modification partielle (une seule caractéristique FR).
            $reload = $this->reloadExcursion($entityManager, $excursionId);
            $this->assertOneTranslationPerLocale($reload);
            $featureCountBefore = $reload->getFeatures()->count();

            $partialPayload = $payload;
            $partialPayload['features'][0]['translations'][0]['title'] = 'Feature A FR modifié '.$suffix;
            $editToken2 = $this->excursionFormToken($client, '/admin/excursions/'.$excursionId.'/edit');
            $this->postEdit($client, $excursionId, $partialPayload, $editToken2);
            self::assertResponseRedirects('/admin/excursions');

            $reload = $this->reloadExcursion($entityManager, $excursionId);
            self::assertSame($featureCountBefore, $reload->getFeatures()->count());
            $titles = [];
            foreach ($reload->getFeatures() as $feature) {
                foreach ($feature->getTranslations() as $t) { $titles[$t->getLocale()][] = $t->getTitle(); }
            }
            self::assertContains('Feature A FR modifié '.$suffix, $titles['fr'], 'La modification ciblée est appliquée.');
            self::assertContains('Feature B FR', $titles['fr'], 'L’autre caractéristique reste intacte.');
            $this->assertOneTranslationPerLocale($reload);
        } finally {
            $this->cleanup($entityManager, $admin, $excursionId, $category, $destination);
        }
    }

    public function testAddUpdateAndDeleteWithinNestedCollectionsBehaves(): void
    {
        [$client, $entityManager, $admin] = $this->createAdmin($suffix = bin2hex(random_bytes(4)));
        [$category, $destination] = $this->createCategoryAndDestination($entityManager, $suffix);
        $excursionId = null;

        try {
            $token = $this->excursionFormToken($client, '/admin/excursions/new');
            $payload = $this->fullPayload((string) $category->getId(), (string) $destination->getId(), $suffix);
            $this->postNew($client, $payload, $token);
            self::assertResponseRedirects('/admin/excursions');

            $excursion = $entityManager->getRepository(Excursion::class)->findOneBy(['slug' => 'dup-excursion-'.$suffix]);
            $excursionId = $excursion->getId();

            // Test D — suppression : on retire la caractéristique d'index 1
            // (☂, la dernière) et on modifie le titre de la 0, comme le poste
            // un navigateur après clic sur « supprimer » (indices contigus).
            $deletePayload = $payload;
            $deletePayload['features'][0]['translations'][0]['title'] = 'Feature A FR modifié';
            unset($deletePayload['features'][1]);

            $editToken = $this->excursionFormToken($client, '/admin/excursions/'.$excursionId.'/edit');
            $this->postEdit($client, $excursionId, $deletePayload, $editToken);
            self::assertResponseRedirects('/admin/excursions');

            $reload = $this->reloadExcursion($entityManager, $excursionId);
            $icons = [];
            foreach ($reload->getFeatures() as $feature) { $icons[] = $feature->getIcon(); }
            self::assertNotContains('☂', $icons, 'La caractéristique supprimée disparaît.');
            self::assertContains('★', $icons, 'La caractéristique conservée reste.');
            self::assertCount(1, $reload->getFeatures());

            // Test D — ajout : une nouvelle caractéristique via le prototype JS
            // (index contigu suivant), la première restant inchangée.
            $addPayload = $deletePayload;
            $addPayload['features'][1] = [
                'icon' => '⚑', 'position' => '1',
                'translations' => [
                    ['locale' => 'fr', 'title' => 'Feature C FR', 'subtitle' => ''],
                    ['locale' => 'en', 'title' => 'Feature C EN', 'subtitle' => ''],
                    ['locale' => 'it', 'title' => '', 'subtitle' => ''],
                ],
            ];

            $editToken2 = $this->excursionFormToken($client, '/admin/excursions/'.$excursionId.'/edit');
            $this->postEdit($client, $excursionId, $addPayload, $editToken2);
            self::assertResponseRedirects('/admin/excursions');

            $reload = $this->reloadExcursion($entityManager, $excursionId);
            $icons = [];
            foreach ($reload->getFeatures() as $feature) { $icons[] = $feature->getIcon(); }
            self::assertContains('⚑', $icons, 'La nouvelle caractéristique est ajoutée.');
            self::assertContains('★', $icons, 'La caractéristique conservée reste.');
            self::assertNotContains('☂', $icons, 'La suppression est maintenue après ajout.');

            $titles = [];
            foreach ($reload->getFeatures() as $feature) {
                foreach ($feature->getTranslations() as $t) { $titles[] = $t->getTitle(); }
            }
            self::assertContains('Feature A FR modifié', $titles, 'La modification est conservée.');
            self::assertNotContains('Feature B FR', $titles, 'Les traductions de l’entité supprimée suivent la suppression.');

            // L'ajout/suppression n'a créé aucun doublon en base.
            $this->assertNoDuplicateLocaleRowsInDatabase();
            $this->assertOneTranslationPerLocale($reload);
        } finally {
            $this->cleanup($entityManager, $admin, $excursionId, $category, $destination);
        }
    }

    public function testTamperedPayloadWithTwoFrenchLocalesCannotCreateDuplicates(): void
    {
        [$client, $entityManager, $admin] = $this->createAdmin($suffix = bin2hex(random_bytes(4)));
        [$category, $destination] = $this->createCategoryAndDestination($entityManager, $suffix);
        $excursionId = null;

        try {
            $token = $this->excursionFormToken($client, '/admin/excursions/new');
            $payload = $this->fullPayload((string) $category->getId(), (string) $destination->getId(), $suffix);
            // Charge malveillante/corrompue : deux entrées 'fr' pour la même caractéristique.
            $payload['features'][0]['translations'][2] = ['locale' => 'fr', 'title' => 'Doublon FR', 'subtitle' => ''];

            $this->postNew($client, $payload, $token);
            self::assertResponseRedirects('/admin/excursions', null, 'Même avec un payload corrompu, aucun 1062 ne doit survenir.');

            $excursion = $entityManager->getRepository(Excursion::class)->findOneBy(['slug' => 'dup-excursion-'.$suffix]);
            $excursionId = $excursion->getId();
            $this->assertOneTranslationPerLocale($excursion);
            $this->assertNoDuplicateLocaleRowsInDatabase();
        } finally {
            $this->cleanup($entityManager, $admin, $excursionId, $category, $destination);
        }
    }

    public function testExcursionListsShowAndEditPagesStillWorkAfterChanges(): void
    {
        [$client, $entityManager, $admin] = $this->createAdmin($suffix = bin2hex(random_bytes(4)));
        [$category, $destination] = $this->createCategoryAndDestination($entityManager, $suffix);
        $excursionId = null;

        try {
            $token = $this->excursionFormToken($client, '/admin/excursions/new');
            $payload = $this->fullPayload((string) $category->getId(), (string) $destination->getId(), $suffix);
            $this->postNew($client, $payload, $token);
            self::assertResponseRedirects('/admin/excursions');

            $excursion = $entityManager->getRepository(Excursion::class)->findOneBy(['slug' => 'dup-excursion-'.$suffix]);
            $excursionId = $excursion->getId();

            $client->request('GET', '/admin/excursions');
            self::assertResponseIsSuccessful('La liste des excursions fonctionne.');
            self::assertStringContainsString('Excursion '.$suffix, $client->getResponse()->getContent());

            $crawler = $client->request('GET', '/admin/excursions/'.$excursionId.'/edit');
            self::assertResponseIsSuccessful('La page d’édition fonctionne.');
            self::assertSame(1, $crawler->filter('input[name^="excursion[translations]"][name$="[locale]"][value="fr"]')->count(), 'Les locales voyagent dans des champs cachés.');

            $client->request('GET', '/fr/excursion/dup-excursion-'.$suffix);
            self::assertResponseIsSuccessful('La page de détail publique fonctionne.');
        } finally {
            $this->cleanup($entityManager, $admin, $excursionId, $category, $destination);
        }
    }

    /**
     * Le DOM rendu doit toujours contenir les champs cachées de locale (panneaux
     * rendus et prototypes) : un onglet ouvert avant l'existence de ces champs
     * poste des entrées sans locale, ce qui écrivait null dans setLocale().
     */
    public function testRenderedFormCarriesHiddenLocaleInputsEverywhere(): void
    {
        [$client, $entityManager, $admin] = $this->createAdmin($suffix = bin2hex(random_bytes(4)));
        [$category, $destination] = $this->createCategoryAndDestination($entityManager, $suffix);
        $excursionId = null;

        try {
            $crawler = $client->request('GET', '/admin/excursions/new');
            self::assertResponseIsSuccessful('Le formulaire de création sérender.');
            $html = (string) $client->getResponse()->getContent();

            // Panneaux principaux rendus depuis l'entité : une entrée cachée par locale.
            // Les formulaires d'excursion n'exposent plus que FR et EN (l'italien a
            // été retiré des formulaires ; les traductions italiennes existantes ne
            // sont ni créées ni écrasées par ce formulaire).
            preg_match_all('/name="excursion\[translations\]\[\d+\]\[locale\]" value="(fr|en)"/', $html, $matches);
            $mainLocales = $matches[1];
            sort($mainLocales);
            self::assertSame(['en', 'fr'], $mainLocales, 'Les deux panneaux principaux portent un champ caché de locale non vide.');

            // Prototypes JS : chaque section imbriquée doit embarquer les deux locales.
            foreach (['itineraries', 'features', 'inclusions', 'faqs', 'badges'] as $section) {
                $node = $crawler->filter('.collection-container[data-collection="'.$section.'"]')->first();
                self::assertSame(1, $node->count(), 'Le conteneur de collection '.$section.' existe.');
                $prototype = (string) $node->attr('data-prototype');

                preg_match_all('/name="excursion\['.$section.'\]\[__name__\]\[translations\]\[\d+\]\[locale\]" value="(fr|en)"/', $prototype, $matches);
                $prototypeLocales = $matches[1];
                sort($prototypeLocales);
                self::assertSame(['en', 'fr'], $prototypeLocales, 'Le prototype '.$section.' poste toujours une locale valide pour chaque traduction.');
            }

            // Page d'édition : idem pour les entités existantes (panneaux rendus).
            $token = $this->excursionFormToken($client, '/admin/excursions/new');
            $this->postNew($client, $this->fullPayload((string) $category->getId(), (string) $destination->getId(), $suffix), $token);
            self::assertResponseRedirects('/admin/excursions');
            $excursion = $entityManager->getRepository(Excursion::class)->findOneBy(['slug' => 'dup-excursion-'.$suffix]);
            $excursionId = $excursion->getId();

            $crawler = $client->request('GET', '/admin/excursions/'.$excursionId.'/edit');
            self::assertResponseIsSuccessful('La page d’édition sérender.');
            $html = (string) $client->getResponse()->getContent();

            foreach (['itineraries', 'features', 'inclusions', 'faqs', 'badges'] as $section) {
                // Les traductions vides (« it ») étant nettoyées à la persistance,
                // la page d'édition rend au minimum les panneaux fr + en, chacun
                // portant son champ caché de locale.
                preg_match_all('/name="excursion\['.$section.'\]\[\d+\]\[translations\]\[\d+\]\[locale\]" value="(fr|en|it)"/', $html, $matches);
                self::assertGreaterThanOrEqual(2, \count($matches[0]), 'Chaque traduction rendue de '.$section.' embarque sa locale sur la page d’édition.');
                self::assertContains('fr', $matches[1], 'La locale fr est présente dans '.$section.'.');
            }
        } finally {
            $this->cleanup($entityManager, $admin, $excursionId, $category, $destination);
        }
    }

    /**
     * Rejoue le comportement d'un onglet resté ouvert avant l'ajout des champs
     * cachés de locale : le POST ne contient aucune clé « locale ». Avant le
     * correctif, cela levait « Expected argument of type "string", "null" given
     * at property path "locale" » (ExcursionTranslation::setLocale(null)).
     */
    public function testStaleBrowserPayloadWithoutLocaleFieldsIsSavedWithoutFatalError(): void
    {
        [$client, $entityManager, $admin] = $this->createAdmin($suffix = bin2hex(random_bytes(4)));
        [$category, $destination] = $this->createCategoryAndDestination($entityManager, $suffix);
        $excursionId = null;

        try {
            $payload = $this->stripLocales($this->fullPayload((string) $category->getId(), (string) $destination->getId(), $suffix));
            self::assertStringNotContainsString('locale', json_encode($payload), 'Le payload périmé ne contient aucune clé locale.');

            $token = $this->excursionFormToken($client, '/admin/excursions/new');
            $this->postNew($client, $payload, $token);
            self::assertResponseRedirects('/admin/excursions', null, 'Un POST sans locale ne doit plus jamais planter en 500.');

            $excursion = $this->reloadExcursion($entityManager, (int) $entityManager->getRepository(Excursion::class)->findOneBy(['slug' => 'dup-excursion-'.$suffix])->getId());
            $excursionId = $excursion->getId();

            // Les locales ont été réattribuées par position (ordre de rendu legacy fr, en, it) :
            // le contenu est resté attaché à la bonne langue, sans doublon.
            self::assertSame('Excursion '.$suffix, $excursion->getTranslation('fr')?->getTitle());
            self::assertSame('Excursion '.$suffix.' EN', $excursion->getTranslation('en')?->getTitle());
            // getTranslation() ayant un repli sur fr, on vérifie l'absence de
            // toute locale « it » dans la collection (jamais en passer une entité
            // à une assertion : l'exporteur PHPUnit exploserait sur le graphe Doctrine).
            $mainLocales = array_map(static fn ($t) => $t->getLocale(), $excursion->getTranslations()->toArray());
            sort($mainLocales);
            self::assertSame(['en', 'fr'], $mainLocales, 'La traduction italienne vide est nettoyée, sans doublon.');

            $featureA = null;
            foreach ($excursion->getFeatures() as $feature) {
                if ('★' === $feature->getIcon()) { $featureA = $feature; }
            }
            self::assertNotNull($featureA);
            $byLocale = [];
            foreach ($featureA->getTranslations() as $t) { $byLocale[$t->getLocale()] = $t->getTitle(); }
            self::assertSame('Feature A FR', $byLocale['fr'] ?? null, 'Le contenu français est rattaché à la locale fr.');
            self::assertSame('Feature A EN', $byLocale['en'] ?? null, 'Le contenu anglais est rattaché à la locale en.');

            $badge = $excursion->getBadges()->first();
            $badgeTexts = [];
            foreach ($badge->getTranslations() as $t) { $badgeTexts[$t->getLocale()] = $t->getText(); }
            self::assertSame('Best-seller FR', $badgeTexts['fr'] ?? null);
            self::assertSame('Best-seller EN', $badgeTexts['en'] ?? null);

            $this->assertOneTranslationPerLocale($excursion);
            $this->assertNoDuplicateLocaleRowsInDatabase();
        } finally {
            $this->cleanup($entityManager, $admin, $excursionId, $category, $destination);
        }
    }

    /**
     * Retire toute clé « locale » du payload, comme l'aurait posté un DOM
     * antérieur à l'ajout des champs cachés.
     */
    private function stripLocales(array $payload): array
    {
        foreach ($payload['translations'] as $i => $translation) {
            unset($payload['translations'][$i]['locale']);
        }
        foreach (['itineraries', 'features', 'inclusions', 'faqs', 'badges'] as $section) {
            foreach ($payload[$section] as $i => $entry) {
                foreach ($entry['translations'] as $j => $translation) {
                    unset($payload[$section][$i]['translations'][$j]['locale']);
                }
            }
        }

        return $payload;
    }

    /** @return array<string,int> */
    private function translationRowCounts(): array
    {
        return [
            'excursion' => (int) $this->connection->fetchOne('SELECT COUNT(*) FROM excursion_translation'),
            'itinerary' => (int) $this->connection->fetchOne('SELECT COUNT(*) FROM excursion_itinerary_translation'),
            'feature' => (int) $this->connection->fetchOne('SELECT COUNT(*) FROM excursion_feature_translation'),
            'inclusion' => (int) $this->connection->fetchOne('SELECT COUNT(*) FROM excursion_inclusion_translation'),
            'faq' => (int) $this->connection->fetchOne('SELECT COUNT(*) FROM excursion_faq_translation'),
            'badge' => (int) $this->connection->fetchOne('SELECT COUNT(*) FROM excursion_badge_translation'),
        ];
    }

    private function cleanup(EntityManagerInterface $entityManager, User $admin, ?int $excursionId, Category $category, Destination $destination): void
    {
        $entityManager->clear();

        if (null !== $excursionId) {
            $excursion = $entityManager->getRepository(Excursion::class)->find($excursionId);
            if (null !== $excursion) {
                $entityManager->remove($excursion);
            }
        }

        $managedCategory = $entityManager->getRepository(Category::class)->find($category->getId());
        if (null !== $managedCategory) {
            $entityManager->remove($managedCategory);
        }
        $managedDestination = $entityManager->getRepository(Destination::class)->find($destination->getId());
        if (null !== $managedDestination) {
            $entityManager->remove($managedDestination);
        }

        $managedAdmin = $entityManager->getRepository(User::class)->find($admin->getId());
        if (null !== $managedAdmin) {
            $entityManager->remove($managedAdmin);
        }

        $entityManager->flush();
        $entityManager->clear();
    }
}
