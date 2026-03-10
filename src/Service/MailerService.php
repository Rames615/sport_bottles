<?php

namespace App\Service;

use App\Entity\Order;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Psr\Log\LoggerInterface;

class MailerService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * Sends the contact form message to the site admin,
     * then fires a confirmation back to the visitor.
     *
     * @param array{name: string, email: string, message: string, subject?: string} $context
     *
     * @throws \RuntimeException when the transport fails
     */
    public function sendContactEmail(string $to, string $subject, array $context): void
    {
        // Rename 'email' to 'sender_email' — Symfony reserves 'email' in TemplatedEmail context
        $senderEmail = $context['email'];
        $senderName  = $context['name'];
        $tplContext = [
            'sender_email' => $senderEmail,
            'name'         => $senderName,
            'subject'      => $context['subject'] ?? null,
            'message'      => $context['message'],
        ];

        // ── 1. Notification to admin ─────────────────────────────────────
        $adminEmail = (new TemplatedEmail())
            ->from(new Address('no-reply@sportsbottles.fr', 'SportBottles Contact'))
            ->to($to)
            ->replyTo(new Address($senderEmail, $senderName))
            ->subject($subject)
            ->htmlTemplate('emails/contact/admin_notification.html.twig')
            ->textTemplate('emails/contact/admin_notification.txt.twig')
            ->context(array_merge($tplContext, ['receivedAt' => new \DateTimeImmutable()]));

        // ── 2. Confirmation to the visitor ───────────────────────────────
        $confirmEmail = (new TemplatedEmail())
            ->from(new Address('no-reply@sportsbottles.fr', 'SportBottles'))
            ->to(new Address($senderEmail, $senderName))
            ->subject('Nous avons bien reçu votre message — SportBottles')
            ->htmlTemplate('emails/contact/user_confirmation.html.twig')
            ->textTemplate('emails/contact/user_confirmation.txt.twig')
            ->context($tplContext);

        // Send admin notification — this is the critical email
        try {
            $this->mailer->send($adminEmail);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Mailer error (admin notification): ' . $e->getMessage(), ['exception' => $e]);
            throw new \RuntimeException('Impossible d\'envoyer l\'email. Réessayez plus tard.');
        }

        // Send visitor confirmation — non-critical, log failures but don't show error
        try {
            sleep(2); // Mailtrap free plan: max 1 email/second — 2s to be safe
            $this->mailer->send($confirmEmail);
        } catch (TransportExceptionInterface $e) {
            $this->logger->warning('Mailer error (visitor confirmation): ' . $e->getMessage(), ['exception' => $e]);
        }
    }

    /**
     * Sends an order confirmation email to the customer with full order details.
     *
     * @param array<int, array{name: string, quantity: int, unitPrice: float, subtotal: float}> $items
     *
     * @throws \RuntimeException when the transport fails
     */
    public function sendOrderConfirmation(Order $order, array $items = []): void
    {
        $user = $order->getUser();
        if (!$user) {
            $this->logger->warning('Cannot send order confirmation: no user on order', ['order_id' => $order->getId()]);
            return;
        }

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@sportsbottles.fr', 'SportBottles'))
            ->to(new Address($user->getEmail(), $user->getEmail()))
            ->subject('Confirmation de commande ' . $order->getReference() . ' — SportBottles')
            ->htmlTemplate('emails/order/confirmation.html.twig')
            ->textTemplate('emails/order/confirmation.txt.twig')
            ->context([
                'order' => $order,
                'items' => $items,
            ]);

        try {
            $this->mailer->send($email);
            $this->logger->info('Order confirmation email sent', ['order_id' => $order->getId(), 'to' => $user->getEmail()]);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Failed to send order confirmation email: ' . $e->getMessage(), [
                'exception' => $e,
                'order_id' => $order->getId(),
            ]);
        }
    }
}