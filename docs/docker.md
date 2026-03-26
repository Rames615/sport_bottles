# 🐳 Guide d'installation Docker — Sports Bottles

## Prérequis

| Outil | Version minimale | Lien |
|-------|-----------------|------|
| **Docker Desktop** | 4.x+ | [docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop/) |
| **Git** | 2.x+ | [git-scm.com](https://git-scm.com/) |

> **Windows** : Docker Desktop doit être installé et **en cours d'exécution** (icône dans la barre des tâches). Activer WSL 2 est recommandé pour de meilleures performances.

---

## Architecture Docker

```
┌──────────────────────────────────────────────────────────────────────────┐
│                          docker-compose.yml                              │
├───────────────────────┬────────────────────────┬─────────────────────────┤
│   sports_bottles_app  │   sports_bottles_db    │   sports_bottles_pma    │
│                       │                        │                         │
│   PHP 8.4 + Nginx     │   MySQL 8.0.30         │   phpMyAdmin latest     │
│   Port: 8080          │   Port: 3307           │   Port: 8081            │
│                       │                        │                         │
│   Volumes:            │   Volume:              │   (pas de volume)       │
│   • bind-mount ./     │   • db_data            │                         │
│   • app_var           │     (persistant)       │                         │
│   • app_vendor        │                        │                         │
└───────────────────────┴────────────────────────┴─────────────────────────┘
```

### Fichiers Docker du projet

| Fichier | Rôle |
|---------|------|
| `Dockerfile` | Image PHP 8.4-fpm-alpine avec extensions (pdo_mysql, intl, gd, zip, opcache), Composer, Nginx et Supervisord. |
| `docker-compose.yml` | Orchestre les services `app` (PHP/Nginx), `db` (MySQL) et `pma` (phpMyAdmin) avec healthcheck sur la base de données. |
| `docker/nginx/default.conf` | Fichier de configuration du site pour Nginx. |
| `docker/supervisord.conf` | Fichier de configuration pour Supervisord, qui gère les processus `php-fpm` et `nginx`. |
| `.dockerignore` | Exclut `vendor/`, `var/`, `.git/`, `docs/`, `tests/` pour accélérer le build. |

### Volumes nommés

Les volumes `app_var` et `app_vendor` isolent les dossiers `var/` et `vendor/` du bind-mount Windows → Linux. Cela **résout les erreurs de cache** (`rmdir: Directory not empty`) et **améliore considérablement les performances** d'I/O sur Windows.

---

## 🚀 Installation rapide (5 étapes)

### 1. Cloner le projet

```bash
git clone <url-du-repo> sports_bottles
cd sports_bottles
```

### 2. Configurer l'environnement

Copier le fichier `.env.local` avec les clés Stripe (demander les clés test à l'équipe) :

```bash
cp .env .env.local
```

Éditer `.env.local` et vérifier ces variables :

```dotenv
DATABASE_URL="mysql://root:root@db:3306/sports_bottles?serverVersion=8.0.30&charset=utf8mb4"
STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

> **Important** : Le `DATABASE_URL` doit pointer vers `db` (nom du service Docker), pas `127.0.0.1`.

### 3. Construire et démarrer les conteneurs

```bash
docker compose up -d --build
```

Ce qui se passe :
- Construction de l'image PHP/Apache depuis le `Dockerfile`
- Démarrage de MySQL avec healthcheck (attend que MySQL soit prêt)
- Démarrage de l'application PHP une fois MySQL healthy

Vérifier que tout est lancé :

```bash
docker compose ps
```

Résultat attendu :

```
NAME                 IMAGE                        STATUS                   PORTS
sports_bottles_app   sports_bottles-app           Up X minutes             0.0.0.0:8080->80/tcp
sports_bottles_db    mysql:8.0.30                 Up X minutes (healthy)   0.0.0.0:3307->3306/tcp
sports_bottles_pma   phpmyadmin/phpmyadmin:latest Up X minutes             0.0.0.0:8081->80/tcp
```

### 4. Installer les dépendances et préparer la base

```bash
# Installer les dépendances PHP (+ cache:clear, assets:install, importmap:install)
docker compose exec app composer install

# Créer la base de données (ignoré si elle existe déjà)
docker compose exec app php bin/console doctrine:database:create --if-not-exists

# Exécuter les migrations
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction

# Charger les données de test (fixtures)
docker compose exec app php bin/console doctrine:fixtures:load --no-interaction
```

### 5. Compiler Tailwind CSS

```bash
docker compose exec app php bin/console tailwind:build
```

---

## ✅ Vérification

Ouvrir le navigateur : **[http://localhost:8080](http://localhost:8080)**

Vérifier les webhooks Stripe :

```bash
# Health check (doit retourner "Webhook endpoint OK")
curl http://localhost:8080/stripe/webhook
curl http://localhost:8080/webhook/stripe
```

---

## 📋 Commandes utiles

| Action | Commande |
|--------|----------|
| Démarrer les conteneurs | `docker compose up -d` |
| Arrêter les conteneurs | `docker compose down` |
| Voir les logs en direct | `docker compose logs -f app` |
| Accéder au shell du conteneur | `docker compose exec app bash` |
| Reconstruire après modif Dockerfile | `docker compose up -d --build` |
| Exécuter une commande Symfony | `docker compose exec app php bin/console <commande>` |
| Voir les routes | `docker compose exec app php bin/console debug:router` |
| Compiler Tailwind en mode watch | `docker compose exec app php bin/console tailwind:build --watch` |
| Vérifier les variables d'env | `docker compose exec app php bin/console debug:dotenv` |
| Se connecter à MySQL | `docker compose exec db mysql -uroot -proot sports_bottles` |
| Ouvrir phpMyAdmin | Navigateur → http://localhost:8081 (connexion automatique) |

---

## 🔑 Stripe Webhook en local

### Méthode 1 : Stripe CLI (recommandée)

Installer Stripe CLI puis :

```bash
stripe login
stripe listen --forward-to localhost:8080/stripe/webhook
```

Stripe CLI affichera un `whsec_...` temporaire → le copier dans `.env.local` comme `STRIPE_WEBHOOK_SECRET`.

### Méthode 2 : Sans Stripe CLI

Le webhook fonctionne sans Stripe CLI en développement. Le `STRIPE_WEBHOOK_SECRET` dans `.env.local` est utilisé pour la vérification de signature. En l'absence de ce secret, le controller accepte les payloads sans vérification (dev uniquement).

### Routes Webhook

| Route | Méthode | Controller |
|-------|---------|------------|
| `/stripe/webhook` | POST/GET | `StripeController::webhook()` |
| `/webhook/stripe` | POST/GET | `WebhookController::stripe()` |

Les deux endpoints gèrent :
- `checkout.session.completed` → confirme la commande et envoie l'email
- `payment_intent.payment_failed` → journalise l'échec de paiement

---

## 🔧 Résolution de problèmes

### Erreur : `Can't create database 'sports_bottles'; database exists`

**Cause** : `MYSQL_DATABASE` dans `docker-compose.yml` crée déjà la base.
**Solution** : Utiliser `--if-not-exists` :

```bash
docker compose exec app php bin/console doctrine:database:create --if-not-exists
```

### Erreur : `Failed to remove directory "var/cache/de_"` (cache:clear)

**Cause** : Incompatibilité bind-mount Windows ↔ Linux filesystem.
**Solution** : Les volumes nommés `app_var` et `app_vendor` dans `docker-compose.yml` sont déjà configurés pour résoudre ce problème. Si l'erreur persiste :

```bash
docker compose exec app bash -c "rm -rf var/cache/* && php bin/console cache:clear"
```

### Erreur : `Built Tailwind CSS file does not exist`

**Solution** :

```bash
docker compose exec app php bin/console tailwind:build
```

### Erreur : `empty compose file`

**Cause** : Présence d'un fichier `compose.yaml` vide à côté de `docker-compose.yml`.
**Solution** : Supprimer le fichier vide :

```bash
rm compose.yaml compose.override.yaml
```

### Réinitialisation complète

Pour repartir de zéro (supprime toutes les données) :

```bash
docker compose down -v
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php bin/console doctrine:database:create --if-not-exists
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec app php bin/console doctrine:fixtures:load --no-interaction
docker compose exec app php bin/console tailwind:build
```

---

## 🌐 Ports exposés

| Service | Port local | Port conteneur | URL |
|---------|-----------|----------------|-----|
| Application PHP | 8080 | 80 | http://localhost:8080 |
| MySQL | 3307 | 3306 | `mysql -h 127.0.0.1 -P 3307 -uroot -proot` |
| phpMyAdmin | 8081 | 80 | http://localhost:8081 |
