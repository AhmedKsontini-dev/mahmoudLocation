<?php

namespace App\Controller\Admin;

use App\Config\LocaleConfig;
use App\Entity\Destination;
use App\Entity\DestinationTranslation;
use App\Form\DestinationType;
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

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $destinations = $this->repository->findBy([], ['id' => 'ASC']);
        
        return $this->render('Backend/Destination/index.html.twig', [
            'destinations' => $destinations,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $destination = new Destination();
        $form = $this->createForm(DestinationType::class, $destination);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Auto-generate slug if not provided
            if (!$destination->getSlug()) {
                $frenchName = $destination->getNameTranslated('fr');
                if ($frenchName) {
                    $destination->setSlug($this->slugger->slug($frenchName)->lower());
                }
            }
            
            $this->entityManager->persist($destination);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'La destination a été créée avec succès.');
            
            return $this->redirectToRoute('admin_destination_index');
        }
        
        return $this->render('Backend/Destination/form.html.twig', [
            'form' => $form,
            'title' => 'Nouvelle destination',
            'destination' => null,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Destination $destination): Response
    {
        $form = $this->createForm(DestinationType::class, $destination);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Auto-generate slug if not provided
            if (!$destination->getSlug()) {
                $frenchName = $destination->getNameTranslated('fr');
                if ($frenchName) {
                    $destination->setSlug($this->slugger->slug($frenchName)->lower());
                }
            }
            
            $this->entityManager->flush();
            
            $this->addFlash('success', 'La destination a été modifiée avec succès.');
            
            return $this->redirectToRoute('admin_destination_index');
        }
        
        return $this->render('Backend/Destination/form.html.twig', [
            'form' => $form,
            'title' => 'Modifier la destination',
            'destination' => $destination,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Destination $destination): Response
    {
        // Check if destination has excursions
        if ($destination->getExcursions()->count() > 0) {
            $this->addFlash('error', 'Impossible de supprimer cette destination car elle est utilisée par des excursions.');
            return $this->redirectToRoute('admin_destination_index');
        }
        
        if ($this->isCsrfTokenValid('delete' . $destination->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($destination);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'La destination a été supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }
        
        return $this->redirectToRoute('admin_destination_index');
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
            $destination->setIsActive(true);
            
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
