<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\ShippingAddress;
use App\Entity\User;
use App\Form\ShippingAddressType;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/checkout', name: 'checkout_')]
final class CheckoutController extends AbstractController
{
    public function __construct(
        private \App\Service\CartService $cartService,
    ) {}

    /**
     * STEP 1 — Display and handle the shipping address form.
     * On success, stores the address ID in session and redirects to step 2.
     */
    #[Route('/shipping', name: 'shipping', methods: ['GET', 'POST'])]
    public function shipping(Request $request, EntityManagerInterface $em): Response|RedirectResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $result = $this->cartService->prepareCheckout($user);

        if (!$result['ok']) {
            $this->addFlash('error', $result['message']);
            return $this->redirectToRoute('app_cartindex');
        }

        $shippingAddress = new ShippingAddress();
        $form = $this->createForm(ShippingAddressType::class, $shippingAddress);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $shippingAddress->setUser($user);
            $shippingAddress->setUpdatedAt(new \DateTimeImmutable());
            $em->persist($shippingAddress);
            $em->flush();

            $request->getSession()->set('shipping_address_id', $shippingAddress->getId());

            return $this->redirectToRoute('checkout_confirm');
        }

        return $this->render('checkout/shipping.html.twig', [
            'form'  => $form,
            'cart'  => $result['cart'],
            'total' => $result['total'],
        ]);
    }

    /**
     * STEP 2 — Order summary / confirmation page.
     * Requires a shipping address in session (set in step 1).
     */
    #[Route('/confirm', name: 'confirm', methods: ['GET'])]
    public function confirm(Request $request): Response|RedirectResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $result = $this->cartService->prepareCheckout($user);

        if (!$result['ok']) {
            $this->addFlash('error', $result['message']);
            return $this->redirectToRoute('app_cartindex');
        }

        $shippingAddressId = $request->getSession()->get('shipping_address_id');
        if (!$shippingAddressId) {
            return $this->redirectToRoute('checkout_shipping');
        }

        return $this->render('checkout/confirm.html.twig', [
            'cart'  => $result['cart'],
            'total' => $result['total'],
        ]);
    }

    /**
     * STEP 2 (POST) — Confirm order without Stripe (direct "paid" status).
     * Used when payment is handled outside Stripe (e.g. bank transfer confirmed manually).
     *
     * Flow:
     *   1. Validates user & CSRF token
     *   2. Validates cart
     *   3. Retrieves & verifies shipping address from session
     *   4. Creates Order with status "paid"
     *   5. Clears cart and session, then redirects home
     */
    #[Route('/confirm', name: 'confirm_post', methods: ['POST'])]
    public function confirmOrder(Request $request, EntityManagerInterface $em): RedirectResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('checkout_pay', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('checkout_confirm');
        }

        $result = $this->cartService->prepareCheckout($user);
        if (!$result['ok']) {
            $this->addFlash('error', $result['message']);
            return $this->redirectToRoute('app_cartindex');
        }

        $shippingAddressId = $request->getSession()->get('shipping_address_id');
        if (!$shippingAddressId) {
            return $this->redirectToRoute('checkout_shipping');
        }

        $shippingAddress = $em->getRepository(ShippingAddress::class)->find($shippingAddressId);
        if (!$shippingAddress || $shippingAddress->getUser() !== $user) {
            $this->addFlash('error', 'Adresse de livraison invalide.');
            return $this->redirectToRoute('checkout_shipping');
        }

        $total = (int) round($result['total'] * 100);

        $order = new Order();
        $order->setUser($user);
        $order->setTotalAmount($total);
        $order->setStatus('paid');
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setShippingAddress((string) $shippingAddress);
        $em->persist($order);
        $em->flush();

        // Deduct stock from products based on cart items
        $this->cartService->deductStockForUser($user);

        $this->cartService->clear($user);
        $request->getSession()->remove('shipping_address_id');

        $this->addFlash('success', 'Votre commande a été confirmée avec succès.');
        return $this->redirectToRoute('app_home');
    }

    /**
     * STEP 3 — Payment method selection page.
     *
     * Displays the payment-method.html.twig template which offers:
     *   - Credit/debit card (redirects to Stripe via checkout_pay)
     *   - Digital wallets (Apple Pay, Google Pay, PayPal — UI only, wired to Stripe)
     *   - Bank transfer (wired to checkout_confirm_post)
     *
     * Requires a shipping address in session (set in step 1).
     */
    #[Route('/method', name: 'method', methods: ['GET'])]
    public function method(Request $request, EntityManagerInterface $em): Response|RedirectResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $result = $this->cartService->prepareCheckout($user);

        if (!$result['ok']) {
            $this->addFlash('error', $result['message']);
            return $this->redirectToRoute('app_cartindex');
        }

        // Require a shipping address from the previous step
        $shippingAddressId = $request->getSession()->get('shipping_address_id');
        if (!$shippingAddressId) {
            return $this->redirectToRoute('checkout_shipping');
        }

        // Verify address still belongs to this user
        $shippingAddress = $em->getRepository(ShippingAddress::class)->find($shippingAddressId);
        if (!$shippingAddress || $shippingAddress->getUser() !== $user) {
            $this->addFlash('error', 'Adresse de livraison introuvable. Veuillez la resaisir.');
            return $this->redirectToRoute('checkout_shipping');
        }

        return $this->render('payment/payment-method.html.twig', [
            'cart'            => $result['cart'],
            'total'           => $result['total'],
            'shippingAddress' => $shippingAddress,
        ]);
    }

    /**
     * STEP 4 — Create Stripe Checkout session and redirect to Stripe.
     *
     * Called by the "Carte bancaire" button in payment-method.html.twig.
     * Requires shipping address in session (set in step 1).
     */
    #[Route('/pay', name: 'pay', methods: ['POST'])]
    public function pay(Request $request, EntityManagerInterface $em): RedirectResponse|Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('checkout_pay', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_cartindex');
        }

        $result = $this->cartService->prepareCheckout($user);

        if (!$result['ok']) {
            $this->addFlash('error', $result['message']);
            return $this->redirectToRoute('app_cartindex');
        }

        $shippingAddressId = $request->getSession()->get('shipping_address_id');
        if (!$shippingAddressId) {
            return $this->redirectToRoute('checkout_shipping');
        }

        $shippingAddress = $em->getRepository(ShippingAddress::class)->find($shippingAddressId);
        if (!$shippingAddress || $shippingAddress->getUser() !== $user) {
            $this->addFlash('error', 'Adresse de livraison invalide.');
            return $this->redirectToRoute('checkout_shipping');
        }

        $cart  = $result['cart'];
        $total = (int) round($result['total'] * 100);

        $order = new Order();
        $order->setUser($user);
        $order->setTotalAmount($total);
        $order->setStatus('pending');
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setShippingAddress((string) $shippingAddress);
        $em->persist($order);
        $em->flush();

        $stripeSecret = $_ENV['STRIPE_SECRET_KEY'] ?? null;
        if (!$stripeSecret) {
            $this->addFlash('error', 'Configuration Stripe manquante. Contactez l\'administrateur.');
            return $this->redirectToRoute('app_cartindex');
        }

        Stripe::setApiKey((string) $stripeSecret);

        $lineItems = [];
        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            if ($product) {
                $lineItems[] = [
                    'price_data' => [
                        'currency'     => 'eur',
                        'product_data' => ['name' => $product->getDesignation() ?? ''],
                        'unit_amount'  => (int) round((float) $item->getUnitPrice() * 100),
                    ],
                    'quantity' => $item->getQuantity() ?? 1,
                ];
            }
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => $this->generateUrl('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => $this->generateUrl('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        $stripeUrl = $session->url;
        if (!$stripeUrl) {
            $this->addFlash('error', 'Impossible de créer la session de paiement Stripe.');
            return $this->redirectToRoute('app_cartindex');
        }

        $order->setStripeSessionId($session->id);
        $em->flush();

        $request->getSession()->remove('shipping_address_id');

        return new RedirectResponse($stripeUrl);
    }
}