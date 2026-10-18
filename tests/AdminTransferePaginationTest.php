<?php

namespace App\Tests;

use App\Entity\Transfere;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminTransferePaginationTest extends WebTestCase
{
    private function createAdminUser(EntityManagerInterface $entityManager): User
    {
        $suffix = bin2hex(random_bytes(4));
        $user = (new User())
            ->setEmail('admin-transfere-' . $suffix . '@example.test')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('not-used');

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    /**
     * Nettoyage des entités de test
     *
     * @param Transfere[] $transfers
     */
    private function cleanUpTransfers(EntityManagerInterface $entityManager, array $transfers): void
    {
        foreach ($transfers as $t) {
            $managed = $entityManager->getRepository(Transfere::class)->find($t->getId());
            if ($managed !== null) {
                $entityManager->remove($managed);
            }
        }
        $entityManager->flush();
    }

    public function testTransferePaginationWith25Items(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $admin = $this->createAdminUser($em);
        $client->loginUser($admin);

        $uniqueKeyword = 'TestBatch25_' . bin2hex(random_bytes(3));
        $createdTransfers = [];

        // Créer exactement 25 transferts pour ce test
        for ($i = 1; $i <= 25; $i++) {
            $transfere = (new Transfere())
                ->setLieuDepart($uniqueKeyword . ' Depart ' . $i)
                ->setLieuArrivee('Destination ' . $i)
                ->setPrixNormal((string)(30 + $i))
                ->setPrixSaisonForte((string)(45 + $i))
                ->setActif(true)
                ->setSlug(strtolower($uniqueKeyword) . '-' . $i . '-' . bin2hex(random_bytes(2)));

            $em->persist($transfere);
            $createdTransfers[] = $transfere;
        }
        $em->flush();

        try {
            // --- PAGE 1 ---
            $crawler1 = $client->request('GET', '/admin/transferts?search=' . $uniqueKeyword . '&page=1');
            $this->assertResponseIsSuccessful();

            $rowsPage1 = $crawler1->filter('tbody tr[id^="transfere-row-"]');
            $this->assertCount(10, $rowsPage1, 'La page 1 doit afficher exactement 10 transferts.');

            $content1 = (string) $client->getResponse()->getContent();
            $this->assertStringContainsString('sur <strong>25</strong> transferts', $content1);
            $this->assertStringContainsString('Affichage de <strong>1</strong> à <strong>10</strong>', $content1);

            // Actions existantes sur chaque transfert (Modifier, Voir, Supprimer)
            $this->assertGreaterThanOrEqual(10, $crawler1->filter('a[href*="/edit"]')->count());
            $this->assertGreaterThanOrEqual(10, $crawler1->filter('a[href*="/admin/transferts/"]')->count());
            $this->assertGreaterThanOrEqual(10, $crawler1->filter('form[action*="/delete"]')->count());

            // --- PAGE 2 ---
            $crawler2 = $client->request('GET', '/admin/transferts?search=' . $uniqueKeyword . '&page=2');
            $this->assertResponseIsSuccessful();

            $rowsPage2 = $crawler2->filter('tbody tr[id^="transfere-row-"]');
            $this->assertCount(10, $rowsPage2, 'La page 2 doit afficher exactement 10 transferts.');

            $content2 = (string) $client->getResponse()->getContent();
            $this->assertStringContainsString('Affichage de <strong>11</strong> à <strong>20</strong>', $content2);

            // --- PAGE 3 ---
            $crawler3 = $client->request('GET', '/admin/transferts?search=' . $uniqueKeyword . '&page=3');
            $this->assertResponseIsSuccessful();

            $rowsPage3 = $crawler3->filter('tbody tr[id^="transfere-row-"]');
            $this->assertCount(5, $rowsPage3, 'La page 3 doit afficher exactement 5 transferts (sur 25 au total).');

            $content3 = (string) $client->getResponse()->getContent();
            $this->assertStringContainsString('Affichage de <strong>21</strong> à <strong>25</strong>', $content3);

            // Vérification des liens de pagination Précédent et Suivant
            $this->assertStringContainsString('page=2', $content3);
        } finally {
            $this->cleanUpTransfers($em, $createdTransfers);
        }
    }

    public function testTransferePaginationPreservesSearchFilter(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $admin = $this->createAdminUser($em);
        $client->loginUser($admin);

        $uniqueKeyword = 'FilterSearch_' . bin2hex(random_bytes(3));
        $createdTransfers = [];

        // Créer 15 transferts contenant ce mot-clé spécifique
        for ($i = 1; $i <= 15; $i++) {
            $t = (new Transfere())
                ->setLieuDepart($uniqueKeyword . ' Station ' . $i)
                ->setLieuArrivee('Destination ' . $i)
                ->setPrixNormal('50.00')
                ->setPrixSaisonForte('65.00')
                ->setActif(true)
                ->setSlug(strtolower($uniqueKeyword) . '-' . $i . '-' . bin2hex(random_bytes(2)));
            $em->persist($t);
            $createdTransfers[] = $t;
        }
        $em->flush();

        try {
            $crawler = $client->request('GET', '/admin/transferts?search=' . $uniqueKeyword . '&page=1');
            $this->assertResponseIsSuccessful();

            // Page 1 doit avoir 10 items
            $this->assertCount(10, $crawler->filter('tbody tr[id^="transfere-row-"]'));

            // Les liens de pagination doivent conserver "search="
            $content = (string) $client->getResponse()->getContent();
            $this->assertStringContainsString('search=' . $uniqueKeyword, $content);
            $this->assertStringContainsString('page=2', $content);

            // Aller à la page 2
            $crawlerPage2 = $client->request('GET', '/admin/transferts?search=' . $uniqueKeyword . '&page=2');
            $this->assertResponseIsSuccessful();
            $this->assertCount(5, $crawlerPage2->filter('tbody tr[id^="transfere-row-"]'));
        } finally {
            $this->cleanUpTransfers($em, $createdTransfers);
        }
    }

    public function testDeleteRedirectionWhenPageBecomesEmpty(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $admin = $this->createAdminUser($em);
        $client->loginUser($admin);

        $uniqueKeyword = 'UniqueRoute_' . bin2hex(random_bytes(3));
        $createdTransfers = [];

        // Créer 11 transferts : page 1 = 10, page 2 = 1 seul
        for ($i = 1; $i <= 11; $i++) {
            $t = (new Transfere())
                ->setLieuDepart($uniqueKeyword . ' Depart ' . $i)
                ->setLieuArrivee('Destination ' . $i)
                ->setPrixNormal('40.00')
                ->setPrixSaisonForte('50.00')
                ->setActif(true)
                ->setSlug(strtolower($uniqueKeyword) . '-' . $i . '-' . bin2hex(random_bytes(2)));
            $em->persist($t);
            $createdTransfers[] = $t;
        }
        $em->flush();

        try {
            // Le 11ème transfert est seul sur la page 2
            $lastTransfer = $createdTransfers[10];

            $crawler = $client->request('GET', '/admin/transferts?search=' . $uniqueKeyword . '&page=2');
            $this->assertResponseIsSuccessful();

            $token = $crawler->filter('form[action*="' . $lastTransfer->getId() . '"] input[name="_token"]')->attr('value');

            $client->request('POST', '/admin/transferts/' . $lastTransfer->getId() . '/delete', [
                '_token' => $token,
                'page' => 2,
                'search' => $uniqueKeyword,
                'status' => '',
            ]);

            // La page 2 est devenue vide. Redirection vers la page 1 valide !
            $this->assertResponseRedirects();
            $targetUrl = (string) $client->getResponse()->headers->get('Location');
            $this->assertStringNotContainsString('page=2', $targetUrl);
            $this->assertStringContainsString('search=' . $uniqueKeyword, $targetUrl);
        } finally {
            // Nettoyage des 10 premiers restants
            $this->cleanUpTransfers($em, array_slice($createdTransfers, 0, 10));
        }
    }
}
