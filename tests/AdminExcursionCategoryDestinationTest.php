<?php

namespace App\Tests;

use App\Entity\Category;
use App\Entity\Destination;
use App\Entity\Excursion;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vérifie le fonctionnement des champs Catégorie et Destination du formulaire
 * « Nouvelle excursion » (recherche + création AJAX, sans doublon, réponses JSON).
 */
class AdminExcursionCategoryDestinationTest extends WebTestCase
{
    private function createAdmin(string $suffix): array
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $admin = (new User())
            ->setEmail('admin-excursion-taxo-'.$suffix.'@example.test')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('not-used');

        $entityManager->persist($admin);
        $entityManager->flush();

        $client->loginUser($admin);

        return [$client, $entityManager, $admin];
    }

    /**
     * @return array{string, string} les jetons CSRF lus dans le formulaire d'excursion
     */
    private function openExcursionForm(KernelBrowser $client): array
    {
        $crawler = $client->request('GET', '/admin/excursions/new');
        self::assertResponseIsSuccessful('Le formulaire Nouvelle excursion doit s’afficher.');

        $categoryField = $crawler->filter('.searchable-select[data-field="category"]')->first();
        $destinationField = $crawler->filter('.searchable-select[data-field="destination"]')->first();
        self::assertSame(1, $categoryField->count(), 'Le champ Catégorie doit être présent.');
        self::assertSame(1, $destinationField->count(), 'Le champ Destination doit être présent.');

        // Les URLs appelées par le JavaScript correspondent bien aux routes déclarées.
        self::assertSame('/admin/categories/search', $categoryField->attr('data-url'));
        self::assertSame('/admin/categories/create', $categoryField->attr('data-create-url'));
        self::assertSame('/admin/destinations/search', $destinationField->attr('data-url'));
        self::assertSame('/admin/destinations/create', $destinationField->attr('data-create-url'));

        $categoryToken = (string) $categoryField->attr('data-create-token');
        $destinationToken = (string) $destinationField->attr('data-create-token');
        self::assertNotSame('', $categoryToken, 'La création AJAX de catégorie est protégée par CSRF.');
        self::assertNotSame('', $destinationToken, 'La création AJAX de destination est protégée par CSRF.');

        return [$categoryToken, $destinationToken];
    }

    private function assertJsonResponse(Response $response, int $expectedStatus): array
    {
        self::assertSame($expectedStatus, $response->getStatusCode());
        self::assertStringContainsString('application/json', (string) $response->headers->get('Content-Type'),
            'Les endpoints AJAX doivent toujours renvoyer du JSON, jamais du HTML.');

        $payload = json_decode($response->getContent(), true);
        self::assertIsArray($payload, 'Le corps de la réponse doit être un JSON valide.');

        return $payload;
    }

    public function testNewCategoryIsCreatedAndPersistedAsJson(): void
    {
        [$client, $entityManager, $admin] = $this->createAdmin($suffix = bin2hex(random_bytes(4)));
        [$categoryToken] = $this->openExcursionForm($client);

        try {
            $name = 'Catégorie tests '.$suffix;
            $client->jsonRequest('POST', '/admin/categories/create', ['name' => $name, '_token' => $categoryToken]);
            $payload = $this->assertJsonResponse($client->getResponse(), Response::HTTP_CREATED);

            self::assertFalse($payload['existed'], 'La catégorie a bien été créée.');
            self::assertSame($name, $payload['name']);
            self::assertIsInt($payload['id']);

            $category = $entityManager->getRepository(Category::class)->find($payload['id']);
            self::assertInstanceOf(Category::class, $category, 'La catégorie est enregistrée en base.');
            self::assertSame($name, $category->getNameTranslated('fr'));

            // Elle est immédiatement trouvable par la recherche du champ.
            $client->jsonRequest('GET', '/admin/categories/search?q='.rawurlencode($name));
            $search = $this->assertJsonResponse($client->getResponse(), Response::HTTP_OK);
            self::assertCount(1, $search['results']);
            self::assertSame($payload['id'], $search['results'][0]['id']);
        } finally {
            $this->cleanup($entityManager, $admin, $payload ?? null);
        }
    }

    public function testSameCategoryNameIsReusedWithoutDuplicate(): void
    {
        [$client, $entityManager, $admin] = $this->createAdmin($suffix = bin2hex(random_bytes(4)));
        [$categoryToken] = $this->openExcursionForm($client);

        try {
            $name = 'Catégorie doublon '.$suffix;
            $client->jsonRequest('POST', '/admin/categories/create', ['name' => $name, '_token' => $categoryToken]);
            $created = $this->assertJsonResponse($client->getResponse(), Response::HTTP_CREATED);

            // Même saisie réutilisée : la variante de casse reste le même nom.
            $client->jsonRequest('POST', '/admin/categories/create', ['name' => mb_strtolower($name), '_token' => $categoryToken]);
            $reused = $this->assertJsonResponse($client->getResponse(), Response::HTTP_OK);

            self::assertTrue($reused['existed'], 'La catégorie existante est réutilisée.');
            self::assertSame($created['id'], $reused['id'], 'Aucune seconde catégorie n’est créée.');
            self::assertSame(1, $entityManager->getRepository(Category::class)->count(['slug' => $created['slug']]));
        } finally {
            $this->cleanup($entityManager, $admin, $created ?? null);
        }
    }

    public function testNewDestinationIsCreatedReusedAndPersisted(): void
    {
        [$client, $entityManager, $admin] = $this->createAdmin($suffix = bin2hex(random_bytes(4)));
        [, $destinationToken] = $this->openExcursionForm($client);

        try {
            $name = 'Destination tests '.$suffix;
            $client->jsonRequest('POST', '/admin/destinations/create', ['name' => $name, '_token' => $destinationToken]);
            $created = $this->assertJsonResponse($client->getResponse(), Response::HTTP_CREATED);
            self::assertFalse($created['existed']);

            $destination = $entityManager->getRepository(Destination::class)->find($created['id']);
            self::assertInstanceOf(Destination::class, $destination, 'La destination est enregistrée en base.');
            self::assertSame($name, $destination->getNameTranslated('fr'));

            $client->jsonRequest('POST', '/admin/destinations/create', ['name' => $name, '_token' => $destinationToken]);
            $reused = $this->assertJsonResponse($client->getResponse(), Response::HTTP_OK);
            self::assertTrue($reused['existed']);
            self::assertSame($created['id'], $reused['id'], 'Aucune seconde destination n’est créée.');

            $client->jsonRequest('GET', '/admin/destinations/search?q='.rawurlencode($name));
            $search = $this->assertJsonResponse($client->getResponse(), Response::HTTP_OK);
            self::assertCount(1, $search['results']);
        } finally {
            $this->cleanupDestinations($entityManager, $admin, $created ?? null);
        }
    }

    public function testAjaxEndpointsReturnReadableJsonOnInvalidInput(): void
    {
        [$client, $entityManager, $admin] = $this->createAdmin($suffix = bin2hex(random_bytes(4)));
        [$categoryToken] = $this->openExcursionForm($client);

        try {
            // Saisie vide : erreur 400 en JSON, pas de page HTML.
            $client->jsonRequest('POST', '/admin/categories/create', ['name' => '   ', '_token' => $categoryToken]);
            $empty = $this->assertJsonResponse($client->getResponse(), Response::HTTP_BAD_REQUEST);
            self::assertStringContainsString('obligatoire', $empty['error']);

            // Jeton CSRF absent : refusé, mais toujours en JSON.
            $client->jsonRequest('POST', '/admin/categories/create', ['name' => 'Sans jeton '.$suffix]);
            $this->assertJsonResponse($client->getResponse(), Response::HTTP_FORBIDDEN);

            // Corps JSON mal formé : 400 JSON également.
            $client->request('POST', '/admin/categories/create', [], [], [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ], '{invalid');
            $this->assertJsonResponse($client->getResponse(), Response::HTTP_BAD_REQUEST);

            self::assertSame(0, $entityManager->getRepository(Category::class)->count(['slug' => 'sans-jeton-'.$suffix]),
                'Aucune catégorie n’est enregistrée lorsque la requête est refusée.');
        } finally {
            $this->cleanup($entityManager, $admin, null);
        }
    }

    public function testExcursionIsSavedWithNewCategoryAndDestination(): void
    {
        [$client, $entityManager, $admin] = $this->createAdmin($suffix = bin2hex(random_bytes(4)));
        [$categoryToken, $destinationToken] = $this->openExcursionForm($client);
        $category = null;
        $destination = null;
        $title = 'Excursion taxes '.$suffix;

        try {
            $client->jsonRequest('POST', '/admin/categories/create', [
                'name' => 'Catégorie excursion '.$suffix,
                '_token' => $categoryToken,
            ]);
            $category = $this->assertJsonResponse($client->getResponse(), Response::HTTP_CREATED);

            $client->jsonRequest('POST', '/admin/destinations/create', [
                'name' => 'Destination excursion '.$suffix,
                '_token' => $destinationToken,
            ]);
            $destination = $this->assertJsonResponse($client->getResponse(), Response::HTTP_CREATED);

            $crawler = $client->request('GET', '/admin/excursions/new');
            $form = $crawler->filter('form button[type="submit"]')->first()->form();
            $form['excursion[category]'] = (string) $category['id'];
            $form['excursion[destination]'] = (string) $destination['id'];
            $form['excursion[priceAdult]'] = '120';
            $form['excursion[translations][0][title]'] = $title;
            $form['excursion[translations][0][description]'] = 'Description de test '.$suffix;

            $client->submit($form);
            self::assertResponseRedirects('/admin/excursions');

            $excursion = $entityManager->getRepository(Excursion::class)->findOneBy(['slug' => 'excursion-taxes-'.$suffix]);
            self::assertInstanceOf(Excursion::class, $excursion, 'L’excursion est enregistrée.');
            self::assertSame($category['id'], $excursion->getCategory()?->getId(), 'La catégorie AJAX est liée.');
            self::assertSame($destination['id'], $excursion->getDestination()?->getId(), 'La destination AJAX est liée.');

            // La catégorie et la destination créées en AJAX sont bien proposées au champ
            // lors de l'édition (choix existant réutilisable, plus doublon à recréer).
            $editCrawler = $client->request('GET', '/admin/excursions/'.$excursion->getId().'/edit');
            self::assertResponseIsSuccessful();
            $editForm = $editCrawler->filter('form button[type="submit"]')->first()->form();
            self::assertSame((string) $category['id'], $editForm['excursion[category]']->getValue());
            self::assertSame((string) $destination['id'], $editForm['excursion[destination]']->getValue());

            $entityManager->remove($excursion);
            $entityManager->flush();
        } finally {
            $this->cleanup($entityManager, $admin, $category);
            $this->cleanupDestinations($entityManager, null, $destination);
        }
    }

    /**
     * Le champ Catégorie doit afficher toutes les catégories dès le premier clic,
     * avec leur icône : la recherche sans saisie renvoie la liste complète en JSON.
     */
    public function testCategoryListIsServedWithoutQueryWithIcons(): void
    {
        [$client, $entityManager, $admin] = $this->createAdmin($suffix = bin2hex(random_bytes(4)));
        [$categoryToken] = $this->openExcursionForm($client);

        try {
            $name = 'Catégorie liste '.$suffix;
            $client->jsonRequest('POST', '/admin/categories/create', ['name' => $name, '_token' => $categoryToken]);
            $created = $this->assertJsonResponse($client->getResponse(), Response::HTTP_CREATED);

            // L'icône (emoji) est renseignée sur la catégorie créée.
            $category = $entityManager->getRepository(Category::class)->find($created['id']);
            $category->setIcon('🏖️');
            $entityManager->flush();

            // Sans aucune saisie (q vide), la liste complète est fournie, icônes comprises.
            $client->jsonRequest('GET', '/admin/categories/search?q=');
            $search = $this->assertJsonResponse($client->getResponse(), Response::HTTP_OK);
            self::assertNotEmpty($search['results'], 'La liste complète des catégories doit être servie dès le premier clic.');

            $match = null;
            foreach ($search['results'] as $result) {
                if ($result['id'] === $created['id']) {
                    $match = $result;
                    break;
                }
            }
            self::assertNotNull($match, 'La catégorie créée figure dans la liste sans avoir à rechercher.');
            self::assertSame('🏖️', $match['icon'] ?? null, 'L’icône de la catégorie est fournie pour l’affichage.');
        } finally {
            $this->cleanup($entityManager, $admin, $created ?? null);
        }
    }

    /**
     * Le champ Catégorie porte l'attribut d'ouverture immédiate ; Destination
     * conserve son comportement initial (aucun attribut ajouté).
     */
    public function testCategoryFieldOpensFullListOnClickWithoutTouchingDestination(): void
    {
        [$client, $entityManager, $admin] = $this->createAdmin($suffix = bin2hex(random_bytes(4)));

        try {
            $crawler = $client->request('GET', '/admin/excursions/new');
            self::assertResponseIsSuccessful();

            $categoryField = $crawler->filter('.searchable-select[data-field="category"]')->first();
            self::assertSame('true', $categoryField->attr('data-show-all'),
                'Le champ Catégorie doit afficher la liste complète dès le clic.');

            $destinationField = $crawler->filter('.searchable-select[data-field="destination"]')->first();
            self::assertNull($destinationField->attr('data-show-all'),
                'Le champ Destination doit conserver son comportement initial.');
        } finally {
            $this->cleanup($entityManager, $admin, null);
        }
    }

    private function cleanup(EntityManagerInterface $entityManager, ?User $admin, ?array $created): void
    {
        if (null !== $admin) {
            $managed = $entityManager->getRepository(User::class)->find($admin->getId());
            if (null !== $managed) {
                $entityManager->remove($managed);
            }
        }

        if (null !== $created && isset($created['id'])) {
            $category = $entityManager->getRepository(Category::class)->find($created['id']);
            if (null !== $category) {
                $entityManager->remove($category);
            }
        }

        $entityManager->flush();
        $entityManager->clear();
    }

    private function cleanupDestinations(EntityManagerInterface $entityManager, ?User $admin, ?array $created): void
    {
        if (null !== $admin) {
            $managed = $entityManager->getRepository(User::class)->find($admin->getId());
            if (null !== $managed) {
                $entityManager->remove($managed);
            }
        }

        if (null !== $created && isset($created['id'])) {
            $destination = $entityManager->getRepository(Destination::class)->find($created['id']);
            if (null !== $destination) {
                $entityManager->remove($destination);
            }
        }

        $entityManager->flush();
        $entityManager->clear();
    }
}
