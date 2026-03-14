# Démarrage rapide

## Objectif

Ce document explique comment installer et exécuter Sports Bottles en local dans de bonnes conditions, sans répéter les détails d'architecture déjà décrits dans `docs/readme.md` et `docs/rapport_rncp_sports_bottles.md`.

## Prérequis

- PHP 8.2 ou supérieur ;
- Composer ;
- une base MySQL ou MariaDB accessible ;
- Node.js n'est pas requis pour le fonctionnement courant, le projet s'appuyant principalement sur AssetMapper ;
- un compte Stripe en mode test si vous souhaitez valider le tunnel de paiement.

## Installation

1. Installer les dépendances PHP.

```bash
composer install
```

2. Configurer les variables d'environnement locales dans `.env.local`.

Variables minimales à prévoir :

- `DATABASE_URL` ;
- `MAILER_DSN` ;
- `STRIPE_PUBLIC_KEY` ;
- `STRIPE_SECRET_KEY` ;
- `STRIPE_WEBHOOK_SECRET` pour les tests webhook.

3. Créer la base et appliquer les migrations.

```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate
```

4. Charger les données de démonstration si nécessaire.

```bash
php bin/console doctrine:fixtures:load
```

5. Démarrer l'application.

```bash
symfony serve
```

Alternative simple :

```bash
php -S localhost:8000 -t public
```

## Vérifications minimales après installation

- la page d'accueil s'affiche ;
- le catalogue produit charge correctement ;
- une connexion utilisateur fonctionne ;
- le panier accepte l'ajout d'un produit ;
- l'administration EasyAdmin reste accessible à un compte disposant de `ROLE_ADMIN`.

## Comptes et administration

Pour promouvoir un utilisateur en administrateur :

```bash
php bin/console app:user:promote-admin email@example.com
```

Pour remettre du stock sur des produits épuisés :

```bash
php bin/console app:update-product-stock
```

## Paiement Stripe en local

Le flux Stripe n'est pleinement testable que si :

- les clés Stripe de test sont configurées ;
- le webhook local est exposé via Stripe CLI ou un tunnel.

Voir :

- `docs/STRIPE_SETUP_FR.md`
- `docs/STRIPE_WEBHOOK_LOCAL.md`
- `docs/STRIPE.md`

## Documents à consulter ensuite

- `docs/readme.md` pour la vue d'ensemble du projet ;
- `docs/ARCHITECTURE.md` pour la structure technique ;
- `docs/TEST_GUIDE.md` pour la recette fonctionnelle ;
- `docs/VERIFICATION_CHECKLIST.md` pour les contrôles avant livraison.