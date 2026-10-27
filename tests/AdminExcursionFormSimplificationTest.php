<?php

namespace App\Tests;

use App\Entity\Category;
use App\Entity\Destination;
use App\Entity\Excursion;
use App\Entity\ExcursionTranslation;
use App\Entity\User;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Vérifie la simplification des formulaires d'excursion (création + modification) :
 *  - le champ « icône » est devenu facultatif/nullable pour les caractéristiques et
 *    les inclus/exclus (on peut enregistrer une excursion sans aucune icône) ;
 *  - l'italien n'est plus exposé de façon visible dans les formulaires (seuls FR et
 *    EN sont proposés), tout en préservant les traductions italiennes déjà en base.
 */
class AdminExcursionFormSimplificationTest extends WebTestCase
{
    private Connection $connection;

    private function createAdmin(string $suffix): array
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->connection = $entityManager->getConnection();

        $admin = (new User())
            ->setEmail('admin-excursion-simp-' . $suffix . '@example.test')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('not-used');

        $entityManager->persist($admin);
        $entityManager->flush();
        $client->loginUser($admin);

        return [$client, $entityManager, $admin];
    }

    private function createCategoryAndDestination(EntityManagerInterface $entityManager, string $suffix): array
    {
        $category = (new Category())->setSlug('simp-cat-' . $suffix);
        $destination = (new Destination())->setSlug('simp-des-' . $suffix);
        $entityManager->persist($category);
        $entityManager->persist($destination);
        $entityManager->flush();

        return [$category, $destination];
    }

    private function formToken($client, string $url): string
    {
        $crawler = $client->request('GET', $url);
        self::assertResponseIsSuccessful('Le formulaire excursion doit être rendu.');

        return (string) $crawler->filter('input[name="excursion[_token]"]')->first()->attr('value');
    }

    /**
     * Payload minimal contenant une caractéristique et un inclus/exclus SANS la clé
     * « icon » (le champ étant masqué et devenu facultatif). Position conservée car
     * toujours requise.
     */
    private function payloadWithoutIcons(string $category, string $destination, string $suffix): array
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
            'priceAdult' => '50.00',
            'priceChild' => '25.00',
            'minParticipants' => '1',
            'maxParticipants' => '12',
            'cancellationHours' => '24',
            'slug' => 'simp-excursion-' . $suffix,
            'translations' => [
                ['locale' => 'fr', 'title' => 'Excursion ' . $suffix, 'description' => 'Description FR'],
                ['locale' => 'en', 'title' => 'Excursion ' . $suffix . ' EN', 'description' => ''],
            ],
            'features' => [
                [
                    'position' => '0',
                    'translations' => [
                        ['locale' => 'fr', 'title' => 'Feature sans icône FR', 'subtitle' => ''],
                    ],
                ],
            ],
            'inclusions' => [
                [
                    'type' => 'included',
                    'position' => '0',
                    'translations' => [
                        ['locale' => 'fr', 'text' => 'Inclus sans icône FR'],
                    ],
                ],
            ],
        ];
    }

    public function testFeatureAndInclusionCanBeSavedWithoutIcon(): void
    {
        [$client, $entityManager, $admin] = $this->createAdmin($suffix = bin2hex(random_bytes(4)));
        [$category, $destination] = $this->createCategoryAndDestination($entityManager, $suffix);
        $excursionId = null;

        try {
            $token = $this->formToken($client, '/admin/excursions/new');
            $client->request('POST', '/admin/excursions/new', [
                'excursion' => $this->payloadWithoutIcons((string) $category->getId(), (string) $destination->getId(), $suffix) + ['_token' => $token],
            ]);

            // Le formulaire doit être accepté (aucune erreur de validation liée à
            // l'absence d'icône) puis rediriger vers la liste.
            self::assertResponseRedirects('/admin/excursions');

            $excursionId = (int) $this->connection->fetchOne(
                'SELECT id FROM excursion WHERE slug = ?',
                ['simp-excursion-' . $suffix]
            );
            self::assertGreaterThan(0, $excursionId, 'L\'excursion sans icône a été créée.');

            // La caractéristique et l'inclus sont enregistrés avec une icône NULL.
            $featureIcon = $this->connection->fetchOne(
                'SELECT icon FROM excursion_feature WHERE excursion_id = ?',
                [$excursionId]
            );
            self::assertNull($featureIcon, 'La caractéristique est enregistrée sans icône (NULL).');

            $inclusionIcon = $this->connection->fetchOne(
                'SELECT icon FROM excursion_inclusion WHERE excursion_id = ?',
                [$excursionId]
            );
            self::assertNull($inclusionIcon, 'L\'inclus/exclus est enregistré sans icône (NULL).');
        } finally {
            $this->cleanup($entityManager, $admin, $excursionId, $category, $destination);
        }
    }

    public function testNewExcursionFormExposesOnlyFrenchAndEnglish(): void
    {
        [$client, $entityManager, $admin] = $this->createAdmin($suffix = bin2hex(random_bytes(4)));
        $this->connection = $entityManager->getConnection();

        try {
            $client->request('GET', '/admin/excursions/new');
            $html = (string) $client->getResponse()->getContent();
            self::assertResponseIsSuccessful();

            // Aucun champ caché de locale italienne n'est rendu (les formulaires ne
            // créent plus d'italien) et l'onglet « Italiano » a disparu.
            self::assertStringNotContainsString('Italiano', $html, 'L\'onglet italien n\'est plus affiché.');
            self::assertSame(
                0,
                preg_match_all('/\[locale\]"[^>]*value="it"/', $html),
                'Aucune entrée de locale italienne n\'est présente dans le formulaire de création.'
            );

            // FR et EN restent bien présents.
            self::assertMatchesRegularExpression('/\[locale\]"[^>]*value="fr"/', $html);
            self::assertMatchesRegularExpression('/\[locale\]"[^>]*value="en"/', $html);
        } finally {
            $entityManager->remove($admin);
            $entityManager->flush();
        }
    }

    public function testExistingItalianIsHiddenButPreservedOnEditPage(): void
    {
        [$client, $entityManager, $admin] = $this->createAdmin($suffix = bin2hex(random_bytes(4)));
        [$category, $destination] = $this->createCategoryAndDestination($entityManager, $suffix);
        $this->connection = $entityManager->getConnection();
        $excursionId = null;

        try {
            $token = $this->formToken($client, '/admin/excursions/new');
            $client->request('POST', '/admin/excursions/new', [
                'excursion' => $this->payloadWithoutIcons((string) $category->getId(), (string) $destination->getId(), $suffix) + ['_token' => $token],
            ]);
            self::assertResponseRedirects('/admin/excursions');

            $excursionId = (int) $this->connection->fetchOne(
                'SELECT id FROM excursion WHERE slug = ?',
                ['simp-excursion-' . $suffix]
            );

            // On ajoute arbitrairement une traduction italienne déjà « enregistrée ».
            $entityManager->clear();
            $excursion = $entityManager->getRepository(Excursion::class)->find($excursionId);
            $italian = (new ExcursionTranslation())->setLocale('it')->setTitle('Escursione IT')->setDescription('Descrizione IT');
            $excursion->addTranslation($italian);
            $entityManager->persist($italian);
            $entityManager->flush();

            // Page d'édition : pas d'onglet italien visible, mais la valeur existante
            // voyage dans un conteneur caché (champ [locale] = "it" en display:none).
            $client->request('GET', '/admin/excursions/' . $excursionId . '/edit');
            $html = (string) $client->getResponse()->getContent();
            self::assertResponseIsSuccessful();
            self::assertStringNotContainsString('🇮🇹 Italiano', $html, 'Aucun onglet italien visible en édition.');
            self::assertStringContainsString('translation-locale-hidden', $html, 'L\'italien existant reste transmis via un conteneur caché.');

            // Enregistrement sans toucher à l'italien : le navigateur poste le
            // conteneur caché italien (valeur existante), la ligne est conservée.
            $editToken = $this->formToken($client, '/admin/excursions/' . $excursionId . '/edit');
            $editPayload = $this->payloadWithoutIcons((string) $category->getId(), (string) $destination->getId(), $suffix);
            // Rejoue ce que soumet le champ caché de l'onglet italien en édition.
            $editPayload['translations'][] = ['locale' => 'it', 'title' => 'Escursione IT', 'description' => 'Descrizione IT', 'subtitle' => '', 'location' => '', 'metaTitle' => '', 'metaDescription' => ''];
            $client->request('POST', '/admin/excursions/' . $excursionId . '/edit', [
                'excursion' => $editPayload + ['_token' => $editToken],
            ]);
            self::assertResponseRedirects('/admin/excursions');

            $itCount = (int) $this->connection->fetchOne(
                'SELECT COUNT(*) FROM excursion_translation WHERE excursion_id = ? AND locale = \'it\' AND title = \'Escursione IT\'',
                [$excursionId]
            );
            self::assertSame(1, $itCount, 'La traduction italienne déjà enregistrée n\'est pas supprimée.');
        } finally {
            $this->cleanup($entityManager, $admin, $excursionId, $category, $destination);
        }
    }

    private function cleanup(EntityManagerInterface $entityManager, User $admin, ?int $excursionId, Category $category, Destination $destination): void
    {
        $entityManager->clear();
        if ($excursionId !== null) {
            $excursion = $entityManager->getRepository(Excursion::class)->find($excursionId);
            if ($excursion !== null) {
                $entityManager->remove($excursion);
            }
        }
        $entityManager->remove($entityManager->getReference(Category::class, $category->getId()));
        $entityManager->remove($entityManager->getReference(Destination::class, $destination->getId()));
        $entityManager->remove($entityManager->getReference(User::class, $admin->getId()));
        $entityManager->flush();
    }
}
