<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly TranslatorInterface $translator,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $username = trim((string) $request->request->get('_username'));
        $user = $username !== '' ? $this->userRepository->findOneBy(['email' => $username]) : null;
        $message = $user
            ? $this->translator->trans('auth.invalid_password')
            : $this->translator->trans('auth.account_not_found');

        if ($request->isXmlHttpRequest() || $request->headers->get('Accept') === 'application/json') {
            return new JsonResponse([
                'errors' => [
                    'email' => $user ? [] : [$message],
                    'password' => $user ? [$message] : [],
                    'form' => [],
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $targetPath = $request->request->get('_target_path');
        if (is_string($targetPath) && str_starts_with($targetPath, '/') && !str_starts_with($targetPath, '//')) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }
}
