<?php

namespace App\Controller\Security\Registration;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'message' => $translator->trans('auth.registration_success'),
                ], Response::HTTP_CREATED);
            }

            // do anything else you need here, like send an email

            $targetPath = $request->request->get('_target_path');
            if (is_string($targetPath) && str_starts_with($targetPath, '/') && !str_starts_with($targetPath, '//')) {
                return $this->redirect($targetPath);
            }

            return $this->redirectToRoute('app_home');
        }

        if ($request->isXmlHttpRequest()) {
            $errors = ['email' => [], 'plainPassword' => [], 'agreeTerms' => [], 'form' => []];
            foreach ($form->getErrors() as $formError) {
                $errors['form'][] = $formError->getMessage();
            }
            foreach ($form->all() as $fieldName => $field) {
                foreach ($field->getErrors(true) as $formError) {
                    $errors[$fieldName][] = $formError->getMessage();
                }
            }

            return new JsonResponse(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
            'auth_initial' => 'register'
        ]);
    }
}
