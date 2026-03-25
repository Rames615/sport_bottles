/**
 * payment.js — Stripe payment form logic
 * Dynamic config (Stripe key, API URLs) is injected via window.PaymentConfig
 * defined by a small inline <script> in payment.html.twig.
 */

(function () {
    'use strict';

    const config = window.PaymentConfig || {};

    // Initialize Stripe
    const stripe = Stripe(config.stripePublicKey);
    const elements = stripe.elements();
    const cardElement = elements.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#424770',
                fontSmoothing: 'antialiased'
            },
            invalid: {
                color: '#fa755a'
            }
        }
    });

    cardElement.mount('#card-element');

    // Handle real-time validation errors from Stripe
    cardElement.addEventListener('change', function (event) {
        const displayError = document.getElementById('card-errors');
        displayError.textContent = event.error ? event.error.message : '';
    });

    // Bootstrap modals
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    const successModal  = new bootstrap.Modal(document.getElementById('successModal'));

    const form         = document.getElementById('paymentForm');
    const submitButton = document.getElementById('submitButton');

    // Handle form submission
    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const cardholderName = document.getElementById('cardholderName').value.trim();
        if (!cardholderName) {
            document.getElementById('paymentErrors').style.display = 'block';
            document.getElementById('errorMessage').textContent = 'Veuillez entrer le nom du titulaire de la carte.';
            return;
        }

        loadingModal.show();
        submitButton.disabled = true;

        const csrfToken = document.querySelector('input[name="_token"]').value;

        try {
            // Step 1 — Create PaymentIntent
            const intentResponse = await fetch(config.createIntentUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({ _token: csrfToken })
            });

            if (!intentResponse.ok) {
                throw new Error('Impossible de créer l\'intention de paiement');
            }

            const intentData = await intentResponse.json();
            if (intentData.error) {
                throw new Error(intentData.error);
            }

            // Step 2 — Confirm card payment with Stripe
            const { error, paymentIntent } = await stripe.confirmCardPayment(intentData.clientSecret, {
                payment_method: {
                    card: cardElement,
                    billing_details: { name: cardholderName }
                }
            });

            if (error) {
                throw new Error(error.message);
            }

            // Step 3 — Confirm with backend
            const confirmResponse = await fetch(config.confirmPaymentUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ intentId: paymentIntent.id })
            });

            const confirmData = await confirmResponse.json();
            if (!confirmData.ok) {
                throw new Error(confirmData.message);
            }

            // Show success then redirect
            loadingModal.hide();
            successModal.show();

            setTimeout(() => {
                window.location.href = confirmData.redirectUrl;
            }, 2500);

        } catch (err) {
            loadingModal.hide();
            document.getElementById('paymentErrors').style.display = 'block';
            document.getElementById('errorMessage').innerHTML =
                '<strong>Erreur de paiement:</strong> ' + err.message;
            submitButton.disabled = false;
        }
    });
})();
