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
     * Display shipping address form
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
            $this->addFlash('error', $result['message'] ?? 'Votre panier est vide.');
            return $this->redirectToRoute('app_cartindex');
        }

        $shippingAddress = new ShippingAddress();
        $form = $this->createForm(ShippingAddressType::class, $shippingAddress);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Set user and save address
            $shippingAddress->setUser($user);
            $shippingAddress->setUpdatedAt(new \DateTimeImmutable());
            $em->persist($shippingAddress);
            $em->flush();

            // Store shipping address ID in session for next step
            $request->getSession()->set('shipping_address_id', $shippingAddress->getId());

            // Redirect to confirmation page
            return $this->redirectToRoute('checkout_confirm');
        }

        return $this->render('checkout/shipping.html.twig', [
            'form' => $form,
            'cart' => $result['cart'],
            'total' => $result['total'],
        ]);
    }

    /**
     * Displays the order confirmation page with cart summary before payment.
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
            $this->addFlash('error', $result['message'] ?? 'Votre panier est vide.');
            return $this->redirectToRoute('app_cartindex');
        }

        // Get shipping address from session
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
     * Confirms the order, marks it as paid, and redirects to home page.
     * 
     * This method:
     * 1. Validates the user is authenticated
     * 2. Verifies CSRF token for security
     * 3. Prepares and validates the checkout
     * 4. Retrieves the shipping address from session
     * 5. Creates the Order entity
     * 6. Updates Order status to 'paid'
     * 7. Persists and saves changes to database
     * 8. Clears the cart
     * 9. Adds a success flash message
     * 10. Redirects to home page
     */
    #[Route('/confirm', name: 'confirm_post', methods: ['POST'])]
    public function confirmOrder(Request $request, EntityManagerInterface $em): RedirectResponse
    {
        // Validate user is authenticated
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        // Verify CSRF token for security
        if (!$this->isCsrfTokenValid('checkout_pay', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('checkout_confirm');
        }

        // Prepare checkout and validate cart
        $result = $this->cartService->prepareCheckout($user);
        if (!$result['ok']) {
            $this->addFlash('error', $result['message'] ?? 'Panier vide ou invalide.');
            return $this->redirectToRoute('app_cartindex');
        }

        // Retrieve shipping address ID from session
        $shippingAddressId = $request->getSession()->get('shipping_address_id');
        if (!$shippingAddressId) {
            return $this->redirectToRoute('checkout_shipping');
        }

        // Get the shipping address and verify ownership
        $shippingAddress = $em->getRepository(ShippingAddress::class)->find($shippingAddressId);
        if (!$shippingAddress || $shippingAddress->getUser() !== $user) {
            $this->addFlash('error', 'Adresse de livraison invalide.');
            return $this->redirectToRoute('checkout_shipping');
        }

        // Extract cart and total amount
        $cart = $result['cart'];
        $total = (int) round($result['total'] * 100);

        // Create Order entity
        $order = new Order();
        $order->setUser($user);
        $order->setTotalAmount($total);
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setShippingAddress((string) $shippingAddress);

        // Update the "status" field to "paid"
        $order->setStatus('paid');

        // Persist the order to database
        $em->persist($order);
        $em->flush();

        // Clear the cart after successful order
        $this->cartService->clear($user);

        // Clear session data
        $request->getSession()->remove('shipping_address_id');

        // Add success flash message
        $this->addFlash('success', 'Votre commande a été confirmée avec succès.');

        // Redirect to home page
        return $this->redirectToRoute('app_home');
    }

    /**
     * Creates the Stripe Checkout session and redirects the user to Stripe.
     */
    #[Route('/pay', name: 'pay', methods: ['POST'])]
    public function pay(Request $request, EntityManagerInterface $em): RedirectResponse|Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('checkout_pay', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_cartindex');
        }

        $result = $this->cartService->prepareCheckout($user);

        if (!$result['ok']) {
            $this->addFlash('error', $result['message'] ?? 'Panier vide ou invalide.');
            return $this->redirectToRoute('app_cartindex');
        }

        // Get shipping address from session
        $shippingAddressId = $request->getSession()->get('shipping_address_id');
        if (!$shippingAddressId) {
            return $this->redirectToRoute('checkout_shipping');
        }

        $cart  = $result['cart'];
        $total = (int) round($result['total'] * 100);

        // Get shipping address
        $shippingAddress = $em->getRepository(ShippingAddress::class)->find($shippingAddressId);
        if (!$shippingAddress || $shippingAddress->getUser() !== $user) {
            $this->addFlash('error', 'Adresse de livraison invalide.');
            return $this->redirectToRoute('checkout_shipping');
        }

        // Create the order in DB with status "pending"
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

        Stripe::setApiKey($stripeSecret);

        $lineItems = [];
        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            if ($product) {
                $lineItems[] = [
                    'price_data' => [
                        'currency'     => 'eur',
                        'product_data' => ['name' => $product->getDesignation()],
                        'unit_amount'  => (int) round((float) $item->getUnitPrice() * 100),
                    ],
                    'quantity' => $item->getQuantity(),
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

        $order->setStripeSessionId($session->id);
        $em->flush();

        // Clear session data
        $request->getSession()->remove('shipping_address_id');

        return new RedirectResponse($session->url);
    }
}
