<?php

namespace App\Controller;

use App\Form\ForgotPasswordFormType;
use App\Form\ResetPasswordFormType;
use App\Repository\UserRepository;
use App\Service\PasswordService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class ResetPasswordController extends AbstractController
{
    public function __construct(
        private readonly PasswordService $passwordService,
        private readonly UserRepository $userRepository,
    ) {}

    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password')]
    public function forgotPassword(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(ForgotPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $this->userRepository->findOneBy(['email' => $email]);

            if ($user) {
                try {
                    $token = $this->passwordService->generateResetToken($user);
                    $this->passwordService->sendResetEmail($user, $token);
                } catch (\RuntimeException) {
                    // Logged in PasswordService; show generic message
                }
            }

            // Always show success — prevents user enumeration
            $this->addFlash(
                'success',
                'Si un compte existe avec cette adresse email, un lien de réinitialisation vous a été envoyé. Vérifiez votre boîte de réception.'
            );

            return $this->redirectToRoute('app_forgot_password');
        }

        return $this->render('reset_password/forgot.html.twig', [
            'forgotPasswordForm' => $form,
        ]);
    }

    #[Route('/reinitialiser-mot-de-passe/{token}', name: 'app_reset_password')]
    public function resetPassword(
        string $token,
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = $this->passwordService->validateToken($token);

        if (!$user) {
            $this->addFlash(
                'danger',
                'Ce lien de réinitialisation est invalide ou a expiré. Veuillez refaire une demande.'
            );
            return $this->redirectToRoute('app_forgot_password');
        }

        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $plainPassword)
            );

            $this->passwordService->clearToken($user);

            $this->addFlash(
                'success',
                'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.'
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetPasswordForm' => $form,
        ]);
    }
}
