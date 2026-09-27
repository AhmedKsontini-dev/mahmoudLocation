<?php

namespace App\Controller\Admin;

use App\Config\LocaleConfig;
use App\Entity\Destination;
use App\Entity\DestinationTranslation;
use App\Repository\DestinationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/destinations', name: 'admin_destination_')]
class DestinationController extends AbstractController
{
    public function __construct(
        private DestinationRepository $repository,
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger
    ) {
    }

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        
        $queryBuilder = $this->repository->createQueryBuilder('d')
            ->leftJoin('d.translations', 't')
            ->where('t.name LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(20);
        
        $destinations = $queryBuilder->getQuery()->getResult();
        
        $results = [];
        foreach ($destinations as $destination) {
            $results[] = [
                'id' => $destination->getId(),
                'name' => $destination->getNameTranslated('fr') ?? $destination->getSlug(),
                'slug' => $destination->getSlug(),
            ];
        }
        
        return new JsonResponse(['results' => $results]);
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['name']) || empty(trim($data['name']))) {
                return new JsonResponse(['error' => 'Le nom est requis'], Response::HTTP_BAD_REQUEST);
            }
            
            $destination = new Destination();
            $destination->setSlug($this->slugger->slug($data['name'])->lower());
            $destination->setActive(true);
            
            // Create French translation
            $translation = new DestinationTranslation();
            $translation->setLocale('fr');
            $translation->setName($data['name']);
            $destination->addTranslation($translation);
            
            // Create translations for other locales
            $locales = LocaleConfig::getSupportedLocales();
            foreach ($locales as $locale) {
                if ($locale !== 'fr') {
                    $translation = new DestinationTranslation();
                    $translation->setLocale($locale);
                    $translation->setName($data['name']); // Default to same name
                    $destination->addTranslation($translation);
                }
            }
            
            $this->entityManager->persist($destination);
            $this->entityManager->flush();
            
            return new JsonResponse([
                'id' => $destination->getId(),
                'name' => $destination->getNameTranslated('fr'),
                'slug' => $destination->getSlug(),
            ]);
        } catch (\Exception $e) {
            error_log('Destination creation error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return new JsonResponse(['error' => 'Erreur: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
