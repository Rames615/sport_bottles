# Guide de déploiement — Production sur Hetzner

## Sommaire

1. [Infrastructure](#1-infrastructure)
2. [Préparation du serveur](#2-préparation-du-serveur)
3. [Déploiement initial](#3-déploiement-initial)
4. [Mise à jour / Redéploiement](#4-mise-à-jour--redéploiement)
5. [Logs et débogage](#5-logs-et-débogage)
6. [Cheat sheet Docker](#6-cheat-sheet-docker)
7. [Pièges rencontrés et solutions](#7-pièges-rencontrés-et-solutions)

---

## 1. Infrastructure

| Composant | Valeur |
|-----------|--------|
| VPS | Hetzner CX22 — 2 vCPU, 4 GB RAM |
| OS | Ubuntu 24.04 |
| IP publique | `157.180.35.139` |
| Domaine | `sports-bottles.duckdns.org` |
| Docker | 29.3.1 |
| Docker Compose | v5.1.1 |
| Répertoire projet | `/home/deploy/sports_bottles` |

### Architecture des services

```
Internet (HTTPS 443 / HTTP 80)
          │
          ▼
┌─────────────────────┐
│   Caddy 2-alpine    │  SSL Let's Encrypt automatique
│   ports: 80, 443    │
└──────────┬──────────┘
           │ reverse_proxy → app:80
           ▼
┌─────────────────────────────────────┐
│   sports_bottles_app                │
│   PHP 8.4-fpm-alpine                │
│   Nginx (port 80 interne)           │
│   Supervisord (nginx + php-fpm)     │
│                                     │
│   Volumes:                          │
│   app_var        → /var/www/html/var│
│   products_images→ /var/www/html/   │
│                    public/products_ │
│                    images           │
└──────────┬──────────────────────────┘
           │ TCP 3306 (réseau interne)
           ▼
┌─────────────────────┐
│   sports_bottles_db │
│   MySQL 8.0.30      │
│   Volume: db_data   │
└─────────────────────┘
```

Seul Caddy est exposé sur Internet. L'app et la DB communiquent sur le réseau Docker interne `sports`.

---

## 2. Préparation du serveur

### 2.1 Connexion SSH

```bash
ssh root@157.180.35.139
```

### 2.2 Mise à jour du système

```bash
apt update && apt upgrade -y
```

### 2.3 Installation de Docker

```bash
curl -fsSL https://get.docker.com | sh
```

Vérification :
```bash
docker --version
docker compose version
```

### 2.4 Firewall (UFW)

```bash
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
ufw status
```

### 2.5 Structure de répertoires sur le serveur

```
/home/deploy/sports_bottles/    ← répertoire du projet (git clone)
├── .env.local                  ← variables de production (JAMAIS committé)
├── docker-compose.prod.yml
├── Caddyfile
└── ...
```

---

## 3. Déploiement initial

### Étape 1 — Cloner le dépôt

```bash
cd /home/deploy
git clone https://github.com/Rames615/sport_bottles.git sports_bottles
cd sports_bottles
```

### Étape 2 — Créer le fichier `.env.local`

```bash
nano .env.local
```

Contenu minimal requis :

```dotenv
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=<générer avec: openssl rand -hex 32>

DATABASE_URL="mysql://root:<MYSQL_ROOT_PASSWORD>@db:3306/sports_bottles?serverVersion=8.0.30&charset=utf8mb4"
MYSQL_ROOT_PASSWORD=<mot_de_passe_fort>
MYSQL_DATABASE=sports_bottles

STRIPE_SECRET_KEY=sk_live_...
STRIPE_PUBLIC_KEY=pk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

MAILER_DSN=smtp://login:password@smtp.mailtrap.io:2525
```

> Générer un APP_SECRET : `openssl rand -hex 32`

### Étape 3 — Vérifier le Caddyfile

```bash
cat Caddyfile
```

Doit contenir :
```
sports-bottles.duckdns.org {
    reverse_proxy app:80
}
```

### Étape 4 — Build de l'image Docker

```bash
docker compose -f docker-compose.prod.yml build --progress=plain
```

Le build exécute dans l'ordre :
1. Installation des packages Alpine (nginx, supervisor, icu-dev...)
2. Compilation des extensions PHP (pdo_mysql, gd, intl, opcache)
3. `composer install --no-dev`
4. `assets:install` → copie les assets des bundles (EasyAdmin) vers `public/bundles/`
5. `cache:clear --env=prod`
6. `tailwind:build --minify --env=prod` → compile le CSS
7. `asset-map:compile --env=prod` → fingerprinte les assets dans `public/assets/`

Durée : **5 à 10 minutes** la première fois (sans cache).

### Étape 5 — Démarrer les conteneurs

```bash
docker compose -f docker-compose.prod.yml up -d
```

Vérifier que tout est `Up` :
```bash
docker compose -f docker-compose.prod.yml ps
```

Résultat attendu :
```
NAME                    STATUS
sports_bottles_app      Up
sports_bottles_db       Up (healthy)
sports_bottles_caddy    Up
```

### Étape 6 — Exécuter les migrations

```bash
docker compose -f docker-compose.prod.yml exec app php bin/console doctrine:migrations:migrate --no-interaction
```

### Étape 7 — Importer les données (premier déploiement)

La base est vide après la migration. Importer depuis la machine locale :

**Sur le PC local :**
```bash
/c/laragon/bin/mysql/mysql-8.0.30-winx64/bin/mysqldump -u root sports_bottles > sports_bottles_data.sql
scp sports_bottles_data.sql root@157.180.35.139:/home/deploy/sports_bottles/
```

**Sur le serveur :**
```bash
MYSQL_ROOT_PASSWORD=$(grep MYSQL_ROOT_PASSWORD .env.local | cut -d'=' -f2)
docker compose -f docker-compose.prod.yml exec -T db mysql -uroot -p"$MYSQL_ROOT_PASSWORD" sports_bottles < sports_bottles_data.sql
```

### Étape 8 — Importer les images produits

**Sur le PC local :**
```bash
scp -r public/products_images/* root@157.180.35.139:/tmp/products_images/
```

**Sur le serveur :**
```bash
docker cp /tmp/products_images/. sports_bottles_app:/var/www/html/public/products_images/
docker compose -f docker-compose.prod.yml exec app chown -R www-data:www-data /var/www/html/public/products_images
```

### Étape 9 — Configurer le webhook Stripe

1. Dashboard Stripe → **Développeurs** → **Webhooks** → **Ajouter un endpoint**
2. URL : `https://sports-bottles.duckdns.org/webhook/stripe`
3. Événements : `checkout.session.completed`, `payment_intent.payment_failed`
4. Copier le **Signing secret** → mettre à jour `STRIPE_WEBHOOK_SECRET` dans `.env.local`
5. Redémarrer l'app :
```bash
docker compose -f docker-compose.prod.yml restart app
```

### Étape 10 — Validation

```bash
# Vérifier les logs (pas d'erreur CRITICAL)
docker compose -f docker-compose.prod.yml logs app --tail 20

# Compter les produits en base
MYSQL_ROOT_PASSWORD=$(grep MYSQL_ROOT_PASSWORD .env.local | cut -d'=' -f2)
docker compose -f docker-compose.prod.yml exec -T db mysql -uroot -p"$MYSQL_ROOT_PASSWORD" sports_bottles -e "SELECT COUNT(*) FROM product"
```

Ouvrir `https://sports-bottles.duckdns.org` dans le navigateur.

---

## 4. Mise à jour / Redéploiement

### Workflow standard (nouveau code pushé)

**1. Se connecter au serveur :**
```bash
ssh root@157.180.35.139
cd /home/deploy/sports_bottles
```

**2. Récupérer les changements :**
```bash
git pull
```

**3. Rebuilder l'image :**
```bash
docker compose -f docker-compose.prod.yml build --progress=plain
```

**4. Redémarrer les conteneurs :**
```bash
docker compose -f docker-compose.prod.yml up -d
```

**5. Exécuter les migrations (si nouvelles migrations) :**
```bash
docker compose -f docker-compose.prod.yml exec app php bin/console doctrine:migrations:migrate --no-interaction
```

**6. Vérifier :**
```bash
docker compose -f docker-compose.prod.yml logs app --tail 10
```

> Il n'y a pas de zero-downtime natif dans cette configuration. L'app est indisponible ~30 secondes pendant le rebuild. Pour du zero-downtime, utiliser `docker compose up -d --no-deps --build app` qui rebuild et redémarre uniquement le service `app` sans toucher la DB ou Caddy.

### Mise à jour uniquement de l'app (sans toucher DB/Caddy)

```bash
git pull
docker compose -f docker-compose.prod.yml up -d --no-deps --build app
```

---

## 5. Logs et débogage

### Voir les logs en temps réel

```bash
# Logs de l'app (nginx + php-fpm)
docker compose -f docker-compose.prod.yml logs -f app

# Dernières N lignes
docker compose -f docker-compose.prod.yml logs app --tail 50

# Logs de Caddy (SSL, requêtes)
docker compose -f docker-compose.prod.yml logs -f caddy

# Logs de MySQL
docker compose -f docker-compose.prod.yml logs -f db
```

### Vérifier les conteneurs qui échouent

```bash
# Statut de tous les services
docker compose -f docker-compose.prod.yml ps

# Inspecter un conteneur arrêté
docker inspect sports_bottles_app
```

### Entrer dans le conteneur pour déboguer

```bash
# Shell interactif dans l'app
docker compose -f docker-compose.prod.yml exec app sh

# Exécuter une commande Symfony
docker compose -f docker-compose.prod.yml exec app php bin/console debug:router
docker compose -f docker-compose.prod.yml exec app php bin/console doctrine:schema:validate
```

### Vérifier les variables d'environnement chargées

```bash
docker compose -f docker-compose.prod.yml exec app php bin/console debug:dotenv
```

### Vérifier les ports ouverts sur le serveur

```bash
ss -tlnp | grep -E '80|443|3306'
ufw status
```

### Déboguer une erreur de build Docker

```bash
# Build avec output complet (ne pas utiliser le cache)
docker compose -f docker-compose.prod.yml build --no-cache --progress=plain 2>&1 | tail -50
```

### Requêtes SQL directes

```bash
MYSQL_ROOT_PASSWORD=$(grep MYSQL_ROOT_PASSWORD .env.local | cut -d'=' -f2)
docker compose -f docker-compose.prod.yml exec -T db mysql -uroot -p"$MYSQL_ROOT_PASSWORD" sports_bottles -e "SELECT COUNT(*) FROM product"
```

---

## 6. Cheat sheet Docker

| Action | Commande |
|--------|----------|
| Démarrer tous les services | `docker compose -f docker-compose.prod.yml up -d` |
| Arrêter tous les services | `docker compose -f docker-compose.prod.yml down` |
| Arrêter + supprimer volumes | `docker compose -f docker-compose.prod.yml down -v` ⚠️ |
| Rebuilder et redémarrer | `docker compose -f docker-compose.prod.yml up -d --build` |
| Rebuilder sans cache | `docker compose -f docker-compose.prod.yml build --no-cache` |
| Redémarrer un seul service | `docker compose -f docker-compose.prod.yml restart app` |
| Voir le statut | `docker compose -f docker-compose.prod.yml ps` |
| Logs en direct | `docker compose -f docker-compose.prod.yml logs -f app` |
| Shell dans l'app | `docker compose -f docker-compose.prod.yml exec app sh` |
| Exécuter une migration | `docker compose -f docker-compose.prod.yml exec app php bin/console doctrine:migrations:migrate --no-interaction` |
| Vider le cache Symfony | `docker compose -f docker-compose.prod.yml exec app php bin/console cache:clear` |
| Utilisation CPU/RAM | `docker stats` |
| Espace disque Docker | `docker system df` |
| Nettoyer les images inutilisées | `docker image prune -f` |

> ⚠️ `down -v` supprime toutes les données MySQL. Ne jamais utiliser en production sans backup.

---

## 7. Pièges rencontrés et solutions

### Piège 1 — `var/` est un volume Docker : le CSS disparaît

**Problème :** `tailwind:build` génère `var/tailwind/app.built.css` pendant le build Docker. Mais le volume `app_var` est monté sur `var/` au démarrage du conteneur, écrasant ce fichier.

**Solution :** Utiliser `asset-map:compile` qui copie les assets compilés dans `public/assets/` (pas dans un volume). C'est ce qui est fait dans le Dockerfile actuel.

---

### Piège 2 — `importmap:install` ne trouve rien à installer

**Problème :** `importmap:install` ne crée `public/vendor/` que si les assets ne sont pas déjà dans `assets/vendor/`. Or `assets/vendor/` était dans `.gitignore` → fichiers absents sur le serveur → erreur au rendu.

**Solution :** Retirer `assets/vendor/` du `.gitignore` et committer les fichiers JS (`stimulus.index.js`, `turbo.index.js`).

---

### Piège 3 — `chown` sur un dossier inexistant fait crasher le CMD

**Problème :** Le CMD du Dockerfile faisait `chown public/vendor` avant que le dossier soit créé → exit code non-zéro → supervisord ne démarre jamais → **502 Bad Gateway**.

**Solution :** Toujours utiliser `|| true` pour les `chown` optionnels, ou créer le dossier avec `mkdir -p` avant.

---

### Piège 4 — `MYSQL_USER=root` interdit par MySQL

**Problème :** MySQL 8 interdit de créer un user `root` via `MYSQL_USER` — `root` existe déjà. Le conteneur DB échoue au démarrage avec une erreur incompréhensible.

**Solution :** Ne jamais définir `MYSQL_USER=root` dans les variables MySQL. Si on utilise root directement, utiliser uniquement `MYSQL_ROOT_PASSWORD`.

---

### Piège 5 — `${MYSQL_ROOT_PASSWORD}` non résolu dans docker-compose

**Problème :** Docker Compose ne résout pas les variables `${}` depuis `env_file` dans le bloc `environment:`. Il les lit depuis le shell courant ou le `.env` à la racine.

**Solution :** Supprimer le bloc `environment:` du service `db` et utiliser uniquement `env_file: - .env.local`.

---

### Piège 6 — `.env` absent du build context

**Problème :** Symfony a besoin de `.env` pour bootstrapper (même en prod, il sert de fallback). Si `.env` est dans `.dockerignore`, le conteneur démarre mais Symfony lève une exception.

**Solution :** `.env` (avec uniquement des valeurs placeholder) doit être commité et ne pas être exclu du build context. Seuls `.env.local` et `.env.*.local` doivent être exclus.

---

### Piège 7 — `assets:install` absent → CSS EasyAdmin manquant

**Problème :** Le dashboard admin s'affiche sans CSS car `public/bundles/` est vide. EasyAdmin (et d'autres bundles) publient leurs assets via `assets:install`.

**Solution :** Ajouter `php bin/console assets:install --env=prod` dans le Dockerfile, avant `asset-map:compile`.

---

### Piège 8 — Base de données vide après déploiement

**Problème :** Les migrations créent le schéma mais pas les données. La page s'affiche mais aucun produit n'apparaît.

**Solution :** Exporter la base locale avec `mysqldump` et l'importer sur le serveur via `docker compose exec -T db mysql ...`. Les images produits doivent être copiées séparément avec `scp` + `docker cp`.

---

### Piège 9 — SSL impossible avec une adresse IP

**Problème :** Let's Encrypt ne génère pas de certificat pour une adresse IP, seulement pour un nom de domaine. Caddy échoue silencieusement ou retourne une erreur TLS.

**Solution :** Utiliser un domaine (même gratuit via DuckDNS) avant d'activer Caddy. Configurer l'enregistrement A du domaine vers l'IP du VPS.

---

### Piège 10 — Le build prend trop longtemps

**Cause :** `--no-cache` force la recompilation de toutes les couches.

**Solution :** Utiliser `--no-cache` uniquement quand nécessaire (changement de Dockerfile ou de packages système). Pour un simple changement de code, le cache Docker saute automatiquement les couches inchangées (packages Alpine, extensions PHP, composer install si `composer.lock` n'a pas changé).
