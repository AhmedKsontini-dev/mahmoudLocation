<?php

namespace App\Tests;

use App\Entity\Transfere;
use App\Entity\TransfereTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TransferDestinationCardsTest extends WebTestCase
{
    public function testTransfersAreGroupedByDestinationAndKeepTheirOwnBookingRoutes(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $suffix = bin2hex(random_bytes(5));
        $destination = 'Hammamet-' . $suffix;
        $destinationKey = mb_strtolower($destination);
        $routes = [
            ['Aéroport Tunis-Carthage ' . $suffix, '95.00', '3 h 7 min', 'grouped-tunis-' . $suffix],
            ['Aéroport Enfidha ' . $suffix, '66.00', '2 h', 'grouped-enfidha-' . $suffix],
        ];
        $transfers = [];

        foreach ($routes as [$departure, $price, $duration, $slug]) {
            $transfer = (new Transfere())
                ->setLieuDepart($departure)
                ->setLieuArrivee($destination)
                ->setPrixNormal($price)
                ->setPrixSaisonForte('120.00')
                ->setSlug($slug)
                ->setActif(true);
            $transfer->addTranslation(
                (new TransfereTranslation())
                    ->setLocale('fr')
                    ->setLieuDepart($departure)
                    ->setLieuArrivee($destination)
                    ->setDuree($duration)
            );
            $entityManager->persist($transfer);
            $transfers[] = $transfer;
        }
        $entityManager->flush();

        try {
            $crawler = $client->request('GET', '/fr');
            self::assertResponseIsSuccessful();

            $destinationCards = $crawler->filter(sprintf('article[data-destination-key="%s"]', $destinationKey));
            self::assertCount(1, $destinationCards);
            self::assertSame('66.00', $destinationCards->filter('.destination-price-val')->attr('data-base'));
            self::assertStringContainsString('À partir de', $destinationCards->text());

            self::assertCount(2, $destinationCards->filter('a.transfer-route-row'));
            foreach ($routes as [$departure, $price, $duration, $slug]) {
                $routeLink = $destinationCards->filter(sprintf('a[href="/fr/transfers/%s"]', $slug));
                self::assertCount(1, $routeLink);
                self::assertStringContainsString($departure, $routeLink->text());
                self::assertStringContainsString($duration, $routeLink->text());
                self::assertSame($price, $routeLink->filter('.price-val')->attr('data-base'));
            }
        } finally {
            foreach ($routes as [, , , $slug]) {
                $transfer = $entityManager->getRepository(Transfere::class)->findOneBy(['slug' => $slug]);
                if ($transfer instanceof Transfere) {
                    $entityManager->remove($transfer);
                }
            }
            $entityManager->flush();
        }
    }
}
