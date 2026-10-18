<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users', name: 'admin_user_')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query->get('search', $request->query->get('q', '')));
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        $pagination = $this->userRepository->findPaginatedUsers($search, $page, $limit);

        $stats = [
            'total' => $this->userRepository->countTotalUsers(),
            'admins' => $this->userRepository->countByRole('ROLE_ADMIN'),
            'users' => $this->userRepository->countByRole('ROLE_USER'),
        ];

        // Rendu partiel pour requêtes AJAX optionnelles
        if ($request->isXmlHttpRequest() || $request->query->getBoolean('ajax')) {
            return $this->render('Backend/User/_table_content.html.twig', [
                'users' => $pagination['items'],
                'pagination' => $pagination,
                'search' => $search,
            ]);
        }

        return $this->render('Backend/User/index.html.twig', [
            'users' => $pagination['items'],
            'pagination' => $pagination,
            'search' => $search,
            'stats' => $stats,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user): Response
    {
        $returnSearch = trim((string) $request->query->get('search', ''));
        $returnPage = max(1, $request->query->getInt('page', 1));

        $currentUser = $this->getUser();
        $isSelf = ($currentUser instanceof User && $currentUser->getId() === $user->getId());

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newEmail = $user->getEmail();

            // Vérification anti-doublon d'e-mail
            $existing = $this->userRepository->findOneBy(['email' => $newEmail]);
            if ($existing !== null && $existing->getId() !== $user->getId()) {
                $this->addFlash('error', 'Cette adresse e-mail est déjà utilisée par un autre compte.');

                return $this->render('Backend/User/edit.html.twig', [
                    'user' => $user,
                    'form' => $form->createView(),
                    'isSelf' => $isSelf,
                    'returnSearch' => $returnSearch,
                    'returnPage' => $returnPage,
                ]);
            }

            // Protection administrateur : interdire le retrait de son propre ROLE_ADMIN
            if ($isSelf) {
                /** @var list<string> $submittedRoles */
                $submittedRoles = $form->get('roles')->getData() ?? [];
                if (!in_array('ROLE_ADMIN', $submittedRoles, true)) {
                    $this->addFlash('error', 'Action interdite : vous ne pouvez pas retirer vos propres privilèges d\'administrateur.');

                    return $this->render('Backend/User/edit.html.twig', [
                        'user' => $user,
                        'form' => $form->createView(),
                        'isSelf' => $isSelf,
                        'returnSearch' => $returnSearch,
                        'returnPage' => $returnPage,
                    ]);
                }
            }

            $this->entityManager->flush();

            $this->addFlash('success', sprintf('L\'utilisateur %s (#%d) a été modifié avec succès.', $user->getEmail(), $user->getId()));

            $redirectParams = [];
            if ($returnSearch !== '') {
                $redirectParams['search'] = $returnSearch;
            }
            if ($returnPage > 1) {
                $redirectParams['page'] = $returnPage;
            }

            return $this->redirectToRoute('admin_user_index', $redirectParams);
        }

        return $this->render('Backend/User/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'isSelf' => $isSelf,
            'returnSearch' => $returnSearch,
            'returnPage' => $returnPage,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        $returnSearch = trim((string) $request->query->get('search', ''));
        $returnPage = max(1, $request->query->getInt('page', 1));

        $redirectParams = [];
        if ($returnSearch !== '') {
            $redirectParams['search'] = $returnSearch;
        }
        if ($returnPage > 1) {
            $redirectParams['page'] = $returnPage;
        }

        // Vérification du token CSRF
        if (!$this->isCsrfTokenValid('delete' . $user->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide. L\'opération a été refusée.');

            return $this->redirectToRoute('admin_user_index', $redirectParams);
        }

        // Empêcher la suppression du compte actuellement connecté
        $currentUser = $this->getUser();
        if ($currentUser instanceof User && $currentUser->getId() === $user->getId()) {
            $this->addFlash('error', 'Sécurité : vous ne pouvez pas supprimer votre propre compte administrateur.');

            return $this->redirectToRoute('admin_user_index', $redirectParams);
        }

        $userEmail = $user->getEmail();
        $userId = $user->getId();

        try {
            $this->entityManager->remove($user);
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('L\'utilisateur %s (#%d) a été supprimé avec succès.', $userEmail, $userId));
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Impossible de supprimer cet utilisateur car il est lié à des enregistrements en base de données (réservations, avis ou autres données).');
        }

        return $this->redirectToRoute('admin_user_index', $redirectParams);
    }
}
