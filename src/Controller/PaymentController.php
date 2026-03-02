<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/payment', name: 'payment_')]
final class PaymentController extends AbstractController
{
    public function __construct(
        private \App\Service\CartService $cartService,
    ) {}

    /**
     * Landing page after Stripe redirect.
     * Syncs order status once, then lets JS polling take over.
     */
    #[Route('/success', name: 'success', methods: ['GET'])]
    public function success(Request $request, EntityManagerInterface $em): Response
    {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            return $this->redirectToRoute('app_home');
        }

        $order = $em->getRepository(Order::class)->findOneBy(['stripeSessionId' => $sessionId]);

        if (!$order) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        // One-time sync with Stripe on page load
        $stripeSecret = $_ENV['STRIPE_SECRET_KEY'] ?? null;
        if ($stripeSecret) {
            Stripe::setApiKey($stripeSecret);
            try {
                $stripeSession = Session::retrieve($sessionId);
                if ($stripeSession->payment_status === 'paid' && $order->getStatus() !== 'paid') {
                    $order->setStatus('paid');
                    $em->flush();
                }
            } catch (\Exception) {
                // Non-blocking — JS polling will handle it
            }
        }

        return $this->render('stripe/success.html.twig', [
            'order'     => $order,
            'sessionId' => $sessionId,
        ]);
    }

    /**
     * Polling endpoint — called by JS every 3s to check order status.
     */
    #[Route('/order-status/{sessionId}', name: 'order_status', methods: ['GET'])]
    public function orderStatus(string $sessionId, EntityManagerInterface $em): Response
    {
        $order = $em->getRepository(Order::class)->findOneBy(['stripeSessionId' => $sessionId]);

        if (!$order) {
            return $this->json(['ok' => false, 'message' => 'Commande introuvable.'], 404);
        }

        return $this->json(['ok' => true, 'status' => $order->getStatus()]);
    }

    /**
     * Final confirmation page after payment is confirmed.
     * Clears the cart and displays the success summary.
     */
    #[Route('/complete/{id}', name: 'complete', methods: ['GET'])]
    public function complete(Order $order): Response
    {
        $user = $this->getUser();

        // Session may have been lost after Stripe redirect — fall back to order's user
        $userFromOrder = ($user instanceof User) ? $user : $order->getUser();

        if ($userFromOrder instanceof User) {
            $this->cartService->clear($userFromOrder);
        }

        return $this->render('stripe/complete.html.twig', [
            'order' => $order,
        ]);
    }

    /**
     * Displayed when the user cancels on the Stripe Checkout page.
     */
    #[Route('/cancel', name: 'cancel', methods: ['GET'])]
    public function cancel(): Response
    {
        return $this->render('stripe/cancel.html.twig');
    }
}