<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels des contrôleurs principaux.
 * Vérifie l'accessibilité des routes publiques et les redirections
 * pour les routes protégées.
 *
 * Note : Ces tests nécessitent une base de données de test.
 * Lancer sans ces tests : php bin/phpunit --exclude-group functional
 */
#[Group('functional')]
class SmokeTest extends WebTestCase
{
    // ── Pages publiques ────────────────────────────────────────────────

    public function testHomePageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }

    public function testProductPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/product');

        $this->assertResponseIsSuccessful();
    }

    // ── Pages protégées (redirection vers login) ───────────────────────

    public function testCartRedirectsWhenNotAuthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/panier');

        $this->assertResponseRedirects();
    }

    public function testCheckoutRedirectsWhenNotAuthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/checkout/shipping');

        $this->assertResponseRedirects();
    }
}
