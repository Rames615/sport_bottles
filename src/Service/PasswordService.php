<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PasswordService
{
    private const TOKEN_LIFETIME_SECONDS = 3600; // 1 hour

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepository,
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {}

    /**
     * Generates a cryptographically secure reset token, stores it on the user,
     * and persists to the database.
     */
    public function generateResetToken(User $user): string
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = new \DateTimeImmutable(
            sprintf('+%d seconds', self::TOKEN_LIFETIME_SECONDS)
        );

        $user->setResetToken($token);
        $user->setResetTokenExpiresAt($expiresAt);

        $this->em->flush();

        return $token;
    }

    /**
     * Validates a reset token: finds the user, checks existence and expiry.
     * Returns the User if valid, or null otherwise.
     */
    public function validateToken(string $token): ?User
    {
        $user = $this->userRepository->findOneBy(['resetToken' => $token]);

        if (!$user) {
            return null;
        }

        $expiresAt = $user->getResetTokenExpiresAt();

        if (!$expiresAt || $expiresAt < new \DateTimeImmutable()) {
            $this->clearToken($user);
            return null;
        }

        return $user;
    }

    /**
     * Clears the reset token from the user after successful password change
     * or after expiry.
     */
    public function clearToken(User $user): void
    {
        $user->setResetToken(null);
        $user->setResetTokenExpiresAt(null);
        $this->em->flush();
    }

    /**
     * Sends the password reset email with a link containing the token.
     */
    public function sendResetEmail(User $user, string $token): void
    {
        $resetUrl = $this->urlGenerator->generate(
            'app_reset_password',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@sportsbottles.fr', 'SportBottles'))
            ->to(new Address($user->getEmail(), $user->getEmail()))
            ->subject('Réinitialisation de votre mot de passe — SportBottles')
            ->htmlTemplate('emails/reset_password/reset.html.twig')
            ->textTemplate('emails/reset_password/reset.txt.twig')
            ->context([
                'resetUrl' => $resetUrl,
                'expiresAt' => $user->getResetTokenExpiresAt(),
            ]);

        try {
            $this->mailer->send($email);
            $this->logger->info('Password reset email sent', [
                'user_id' => $user->getId(),
                'to' => $user->getEmail(),
            ]);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(
                'Failed to send password reset email: ' . $e->getMessage(),
                ['exception' => $e, 'user_id' => $user->getId()]
            );
            throw new \RuntimeException(
                'Impossible d\'envoyer l\'email de réinitialisation. Réessayez plus tard.'
            );
        }
    }
}
