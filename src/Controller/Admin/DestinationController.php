<?php

namespace App\Controller\Admin;

use App\Config\LocaleConfig;
use App\Entity\Destination;
use App\Entity\DestinationTranslation;
use App\Form\DestinationType;
use App\Repository\DestinationRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
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
    /** Portée CSRF des créations de destination depuis l'AJAX. */
    public const CREATE_TOKEN_ID = 'admin_destination_create';

    private const MAX_NAME_LENGTH = 255;

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
            // Clean empty translations
            foreach ($destination->getTranslations() as $translation) {
                if ($translation->getLocale() !== 'fr' && empty(trim($translation->getName() ?? ''))) {
                    $destination->removeTranslation($translation);
                    if ($this->entityManager->contains($translation)) {
                        $this->entityManager->remove($translation);
                    }
                }
            }

            // Auto-generate slug if not provided
            if (!$destination->getSlug()) {
                $frenchName = $destination->getNameTranslated('fr');
                if ($frenchName) {
                    $destination->setSlug($this->uniqueSlug($this->slugger->slug($frenchName)->lower(), $destination));
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

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Destination $destination): Response
    {
        $form = $this->createForm(DestinationType::class, $destination);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Clean empty translations
            foreach ($destination->getTranslations() as $translation) {
                if ($translation->getLocale() !== 'fr' && empty(trim($translation->getName() ?? ''))) {
                    $destination->removeTranslation($translation);
                    if ($this->entityManager->contains($translation)) {
                        $this->entityManager->remove($translation);
                    }
                }
            }

            // Auto-generate slug if not provided
            if (!$destination->getSlug()) {
                $frenchName = $destination->getNameTranslated('fr');
                if ($frenchName) {
                    $destination->setSlug($this->uniqueSlug($this->slugger->slug($frenchName)->lower(), $destination));
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

    #[Route('/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['POST'])]
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

    private function uniqueSlug(string $baseSlug, Destination $destination): string
    {
        $slug = $baseSlug;
        $suffix = 2;
        while (true) {
            $existing = $this->repository->findOneBy(['slug' => $slug]);
            if (null === $existing || $existing->getId() === $destination->getId()) {
                return $slug;
            }
            $slug = $baseSlug . '-' . $suffix++;
        }
    }

    /**
     * Recherche JSON utilisée par le champ « Destination » du formulaire d'excursion.
     */
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->query->get('q', ''));

        if ('' === $query) {
            return new JsonResponse(['results' => []]);
        }

        $results = [];
        foreach ($this->repository->searchByTranslatedName($query) as $destination) {
            $results[] = $this->serializeDestination($destination);
        }

        return new JsonResponse(['results' => $results]);
    }

    /**
     * Création JSON utilisée par le champ « Destination » : réutilise une destination existante
     * plutôt que de créer un doublon, et renvoie toujours du JSON (jamais une page HTML).
     */
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = json_decode((string) $request->getContent(), true);

        if (!is_array($payload)) {
            return new JsonResponse(
                ['error' => 'Requête invalide : le corps JSON est absent ou mal formé.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $token = trim((string) $request->headers->get('X-CSRF-Token'));
        if ('' === $token) {
            $token = trim((string) ($payload['_token'] ?? ''));
        }

        if (!$this->isCsrfTokenValid(self::CREATE_TOKEN_ID, $token)) {
            return new JsonResponse(
                ['error' => 'Session expirée. Veuillez recharger la page puis réessayer.'],
                Response::HTTP_FORBIDDEN
            );
        }

        $name = trim((string) ($payload['name'] ?? ''));

        if ('' === $name) {
            return new JsonResponse(
                ['error' => 'Le nom de la destination est obligatoire.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (mb_strlen($name) > self::MAX_NAME_LENGTH) {
            return new JsonResponse(
                ['error' => sprintf('Le nom de la destination ne peut pas dépasser %d caractères.', self::MAX_NAME_LENGTH)],
                Response::HTTP_BAD_REQUEST
            );
        }

        $existing = $this->repository->findOneByTranslatedName($name);
        if ($existing instanceof Destination) {
            return $this->existingDestinationResponse($existing);
        }

        $destination = new Destination();
        $destination->setSlug($this->uniqueSlug($this->slugger->slug($name)->lower(), $destination));
        $destination->setIsActive(true);

        foreach (LocaleConfig::getSupportedLocales() as $locale) {
            $translation = new DestinationTranslation();
            $translation->setLocale($locale);
            $translation->setName($name);
            $destination->addTranslation($translation);
        }

        try {
            $this->entityManager->persist($destination);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            $this->entityManager->clear();

            // Un doublon a été créé entre-temps : on le réutilise au lieu d'échouer.
            $recovered = $this->repository->findOneByTranslatedName($name)
                ?? $this->repository->findOneBy(['slug' => $destination->getSlug()]);

            if ($recovered instanceof Destination) {
                return $this->existingDestinationResponse($recovered);
            }

            error_log('Destination creation conflict: ' . $exception->getMessage());

            return new JsonResponse(
                ['error' => 'Cette destination existe déjà mais n\'a pas pu être récupérée. Réessayez.'],
                Response::HTTP_CONFLICT
            );
        } catch (\Throwable $exception) {
            $this->entityManager->clear();
            error_log('Destination creation error: ' . $exception->getMessage() . "\n" . $exception->getTraceAsString());

            return new JsonResponse(
                ['error' => "La destination n'a pas pu être enregistrée. Réessayez ou signalez le problème à l'administrateur."],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse($this->serializeDestination($destination) + ['existed' => false], Response::HTTP_CREATED);
    }

    private function serializeDestination(Destination $destination): array
    {
        return [
            'id' => $destination->getId(),
            'name' => $destination->getNameTranslated('fr') ?? $destination->getSlug(),
            'slug' => $destination->getSlug(),
        ];
    }

    private function existingDestinationResponse(Destination $destination): JsonResponse
    {
        return new JsonResponse($this->serializeDestination($destination) + ['existed' => true]);
    }
}
