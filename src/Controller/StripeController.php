<?php

namespace App\Controller;

use App\Entity\Order;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class StripeController extends AbstractController
{
    #[Route('/checkout', name: 'stripe_checkout')]
    public function checkout(
        EntityManagerInterface $em,
        ParameterBagInterface $params
    ): RedirectResponse {

        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        // Montant en centimes
        $totalAmount = 5000; // 50.00 €

        // Création de la commande
        $order = new Order();
        $order->setUser($user);
        $order->setTotalAmount($totalAmount);
        $order->setStatus('pending');
        $order->setCreatedAt(new \DateTimeImmutable());

        $em->persist($order);
        $em->flush();

        // Clé Stripe depuis services.yaml ou .env
        Stripe::setApiKey($params->get('stripe.secret_key'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Commande Sports Bottles',
                    ],
                    'unit_amount' => $totalAmount,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl(
                'stripe_success',
                [],
                \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL
            ) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->generateUrl(
                'stripe_cancel',
                [],
                \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);

        $order->setStripeSessionId($session->id);
        $em->flush();

        return new RedirectResponse($session->url);
    }

    #[Route('/success', name: 'stripe_success')]
    public function success(
        Request $request,
        EntityManagerInterface $em
    ): Response {

        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            return $this->redirectToRoute('homepage');
        }

        $order = $em->getRepository(Order::class)
            ->findOneBy(['stripeSessionId' => $sessionId]);

        if (!$order) {
            throw $this->createNotFoundException();
        }

        // Optionnel : vérifier réellement le paiement auprès de Stripe
        if ($order->getStatus() !== 'paid') {
            $order->setStatus('paid');
            $em->flush();
        }

        return $this->render('stripe/success.html.twig', [
            'order' => $order
        ]);
    }

    #[Route('/cancel', name: 'stripe_cancel')]
    public function cancel(): Response
    {
        return $this->render('stripe/cancel.html.twig');
    }
}
