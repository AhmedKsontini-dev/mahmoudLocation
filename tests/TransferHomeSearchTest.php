<?php

namespace App\Tests;

use App\Entity\Transfere;
use App\Repository\TransfereRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TransferHomeSearchTest extends WebTestCase
{
    public function testDestinationsAreFilteredAndRouteIsValidatedOnTheServer(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $repository = static::getContainer()->get(TransfereRepository::class);
        $suffix = bin2hex(random_bytes(5));

        $routes = [
            ['Aéroport Enfidha ' . $suffix, 'Sousse', 'search-enfidha-sousse-' . $suffix],
            ['Aireport Enfidha ' . $suffix, 'Mahdia', 'search-enfidha-mahdia-' . $suffix],
            ['Aéroport Tunis-Carthage ' . $suffix, 'Tunis', 'search-tunis-tunis-' . $suffix],
            ['Aereport Tunis-Carthage ' . $suffix, 'Hammamet', 'search-tunis-hammamet-' . $suffix],
        ];
        $transfers = [];

        foreach ($routes as [$departure, $destination, $slug]) {
            $transfer = (new Transfere())
                ->setLieuDepart($departure)
                ->setLieuArrivee($destination)
                ->setPrixNormal('50.00')
                ->setPrixSaisonForte('65.00')
                ->setSlug($slug)
                ->setActif(true);
            $entityManager->persist($transfer);
            $transfers[] = $transfer;
        }
        $entityManager->flush();

        try {
            $departures = $repository->findDepartureLocations('fr');
            self::assertSame(1, count(array_filter($departures, static fn (string $value): bool => str_contains($value, $suffix) && str_contains($value, 'Enfidha'))));
            self::assertSame(1, count(array_filter($departures, static fn (string $value): bool => str_contains($value, $suffix) && str_contains($value, 'Tunis-Carthage'))));

            $this->assertDestinations($client, $routes[0][0], ['Sousse', 'Mahdia']);
            $this->assertDestinations($client, $routes[2][0], ['Tunis', 'Hammamet']);

            $client->request('GET', '/fr/transfers/search-by-route', [
                'departure' => $routes[0][0],
                'destination' => 'Sousse',
            ]);
            self::assertResponseRedirects('/fr/transfers/' . $routes[0][2]);

            $client->request('GET', '/fr/transfers/search-by-route', [
                'departure' => $routes[0][0],
                'destination' => 'Tunis',
            ]);
            self::assertResponseStatusCodeSame(404);
        } finally {
            foreach ($routes as [, , $slug]) {
                $managedTransfer = $entityManager->getRepository(Transfere::class)->findOneBy(['slug' => $slug]);
                if ($managedTransfer instanceof Transfere) {
                    $entityManager->remove($managedTransfer);
                }
            }
            $entityManager->flush();
        }
    }

    /**
     * @param string[] $expected
     */
    private function assertDestinations(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client, string $departure, array $expected): void
    {
        $client->request('GET', '/fr/transfers/destinations', ['departure' => $departure]);
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
        $response = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame($expected, $response['destinations']);
    }
}
