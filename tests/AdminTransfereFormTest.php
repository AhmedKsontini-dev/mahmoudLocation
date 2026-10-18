<?php

namespace App\Tests;

use App\Entity\Transfere;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminTransfereFormTest extends WebTestCase
{
    public function testNewTransferHidesDescriptionAndCanBeCreatedWithoutIt(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $suffix = bin2hex(random_bytes(5));
        $admin = $this->createAdminUser($entityManager, $suffix);

        try {
            $client->loginUser($admin);
            $crawler = $client->request('GET', '/admin/transferts/new');

            self::assertResponseIsSuccessful();
            self::assertSame(0, $crawler->filter('#transfere_description')->count());
            self::assertSame(1, $crawler->filter('#transfere_prixNormal')->count());
            self::assertSame(1, $crawler->filter('#transfere_prixSaisonForte')->count());
            self::assertSame(1, $crawler->filter('#transfere_actif')->count());
            self::assertSame(1, $crawler->filter('#transfere_imageFile')->count());
            self::assertSame(2, $crawler->filter('input[name$="[lieuDepart]"]')->count());

            $form = $crawler->selectButton('Enregistrer')->form();
            $form['transfere[prixNormal]'] = '120';
            $form['transfere[prixSaisonForte]'] = '150';
            $form['transfere[translations][0][lieuDepart]'] = 'Aéroport test '.$suffix;
            $form['transfere[translations][0][lieuArrivee]'] = 'Destination test '.$suffix;
            $form['transfere[translations][0][duree]'] = '1 h';

            $client->submit($form);

            self::assertResponseRedirects('/admin/transferts');

            $transfer = $entityManager->getRepository(Transfere::class)->findOneBy([
                'lieuDepart' => 'Aéroport test '.$suffix,
            ]);
            self::assertInstanceOf(Transfere::class, $transfer);
            self::assertNull($transfer->getDescription());

            $editCrawler = $client->request('GET', '/admin/transferts/'.$transfer->getId().'/edit');
            self::assertResponseIsSuccessful();
            self::assertSame(1, $editCrawler->filter('#transfere_description')->count());

            $editForm = $editCrawler->selectButton('Enregistrer')->form();
            $editForm['transfere[description]'] = 'Description mise à jour '.$suffix;
            $client->submit($editForm);

            self::assertResponseRedirects('/admin/transferts');
            $entityManager->refresh($transfer);
            self::assertSame('Description mise à jour '.$suffix, $transfer->getDescription());
        } finally {
            $managedAdmin = $entityManager->getRepository(User::class)->find($admin->getId());
            if ($managedAdmin !== null) {
                $entityManager->remove($managedAdmin);
            }

            $transfer = $entityManager->getRepository(Transfere::class)->findOneBy([
                'lieuDepart' => 'Aéroport test '.$suffix,
            ]);
            if ($transfer !== null) {
                $entityManager->remove($transfer);
            }

            $entityManager->flush();
        }
    }

    private function createAdminUser(EntityManagerInterface $entityManager, string $suffix): User
    {
        $admin = (new User())
            ->setEmail('admin-transfer-form-'.$suffix.'@example.test')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('not-used');

        $entityManager->persist($admin);
        $entityManager->flush();

        return $admin;
    }
}
