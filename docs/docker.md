# Docker — Architecture de production (Hetzner)

## Infrastructure

| Composant | Valeur |
|-----------|--------|
| VPS | Hetzner CX22 — Ubuntu 24.04 |
| IP | `157.180.35.139` |
| Domaine | `sports-bottles.duckdns.org` |
| Docker | 29.3.1 |
| Docker Compose | v5.1.1 |
| Répertoire app | `/home/deploy/sports_bottles` |

---

## Architecture des services

```
Internet (HTTPS)
       │
       ▼
┌─────────────────┐
│  Caddy 2-alpine │  :80, :443 — reverse proxy + SSL Let's Encrypt auto
└────────┬────────┘
         │ proxy → app:80
         ▼
┌─────────────────────────────────┐
│  sports_bottles_app             │
│  PHP 8.4-fpm-alpine             │
│  Nginx (port 80 interne)        │
│  Supervisord (nginx + php-fpm)  │
│                                 │
│  Volumes:                       │
│  • app_var → /var/www/html/var  │
│  • products_images →            │
│    /var/www/html/public/        │
│    products_images              │
└────────┬────────────────────────┘
         │ TCP 3306
         ▼
┌─────────────────┐
│  sports_bottles_db              │
│  MySQL 8.0.30   │
│  Volume: db_data                │
└─────────────────┘
```

---

## Fichiers Docker

| Fichier | Rôle |
|---------|------|
| `Dockerfile` | Image de l'application (build complet) |
| `docker-compose.prod.yml` | Orchestration production (app + db + caddy) |
| `Caddyfile` | Reverse proxy avec SSL automatique Let's Encrypt |
| `docker/nginx/default.conf` | Config Nginx interne (port 80, routing PHP) |
| `docker/supervisord.conf` | Gestion des processus nginx + php-fpm |
| `.dockerignore` | Exclut vendor/, var/, .git/, .env.local, tests/ |

---

## Étapes du build Docker (Dockerfile)

Le build se déroule en couches dans cet ordre :

1. **Image de base** : `php:8.4-fpm-alpine`
2. **Packages Alpine** : git, nginx, supervisor, icu-dev, libzip-dev, libpng-dev, etc.
3. **Extensions PHP** : pdo_mysql, zip, intl, gd, opcache
4. **Config nginx + supervisord** copiée dans l'image
5. **Composer** installé depuis `composer:latest`
6. **`composer install --no-dev`** (couche cachée, ne se relance que si composer.json change)
7. **`COPY . .`** — tout le code source copié (hors `.dockerignore`)
8. **`assets:install`** — copie les assets des bundles (EasyAdmin, etc.) vers `public/bundles/`
9. **`cache:clear --env=prod`**
10. **`tailwind:build --minify --env=prod`** — compile le CSS dans `var/tailwind/app.built.css`
11. **`asset-map:compile --env=prod`** — fingerprinte et copie tous les assets dans `public/assets/`
12. **`chown www-data`** sur `var/` et `public/`
13. **CMD** : `chown -R www-data var/ && supervisord`

> `APP_ENV=prod` et `APP_DEBUG=0` sont définis comme variables d'environnement Docker pendant le build.

---

## Variables d'environnement

Toutes les variables de production sont dans **`.env.local`** sur le serveur (jamais committé).

Le fichier `.env.prod.example` dans le repo sert de template.

Variables obligatoires dans `.env.local` :

```dotenv
APP_ENV=prod
APP_SECRET=<secret_32chars>

DATABASE_URL="mysql://root:<MYSQL_ROOT_PASSWORD>@db:3306/sports_bottles?serverVersion=8.0.30&charset=utf8mb4"
MYSQL_ROOT_PASSWORD=<mot_de_passe_root>
MYSQL_DATABASE=sports_bottles

STRIPE_SECRET_KEY=sk_live_...
STRIPE_PUBLIC_KEY=pk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

MAILER_DSN=smtp://login:password@smtp.mailtrap.io:2525
```

---

## Volumes Docker

| Nom | Monté sur | Contenu |
|-----|-----------|---------|
| `db_data` | `/var/lib/mysql` | Données MySQL persistantes |
| `app_var` | `/var/www/html/var` | Cache Symfony, logs, Tailwind buildé |
| `products_images` | `/var/www/html/public/products_images` | Images des produits uploadées |
| `caddy_data` | `/data` | Certificats SSL Let's Encrypt |
| `caddy_config` | `/config` | Config Caddy |

> `public/assets/` (assets compilés avec fingerprint) est **dans l'image**, pas dans un volume — il est recréé à chaque build.

---

## Réseau

Tous les services partagent le réseau interne `sports` (bridge). Seul **Caddy** expose les ports 80 et 443 vers l'extérieur. L'app et la DB ne sont pas accessibles directement depuis Internet.

---

## Commandes de déploiement

### Premier déploiement

```bash
git clone https://github.com/Rames615/sport_bottles.git sports_bottles
cd sports_bottles
nano .env.local          # remplir avec les vraies valeurs
docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml exec app php bin/console doctrine:migrations:migrate --no-interaction
```

### Mise à jour (nouveau code)

```bash
git pull
docker compose -f docker-compose.prod.yml build --progress=plain
docker compose -f docker-compose.prod.yml up -d
docker compose -f docker-compose.prod.yml exec app php bin/console doctrine:migrations:migrate --no-interaction
```

### Commandes utiles

| Action | Commande |
|--------|----------|
| Voir les logs en direct | `docker compose -f docker-compose.prod.yml logs -f app` |
| Shell dans le conteneur | `docker compose -f docker-compose.prod.yml exec app sh` |
| Statut des services | `docker compose -f docker-compose.prod.yml ps` |
| Requête SQL directe | `docker compose -f docker-compose.prod.yml exec -T db mysql -uroot -p"$MYSQL_ROOT_PASSWORD" sports_bottles -e "SELECT ..."` |
| Importer un dump SQL | `docker compose -f docker-compose.prod.yml exec -T db mysql -uroot -p"$MYSQL_ROOT_PASSWORD" sports_bottles < dump.sql` |
| Redémarrer l'app | `docker compose -f docker-compose.prod.yml restart app` |

---

## Résolution de problèmes

### 502 Bad Gateway

Le conteneur `app` ne démarre pas. Vérifier :
```bash
docker compose -f docker-compose.prod.yml logs app --tail 20
```
Cause fréquente : erreur dans le CMD (chown, console...) qui empêche supervisord de démarrer.

### CSS absent (frontend ou admin)

- **Frontend** : `public/assets/` doit être peuplé par `asset-map:compile` au build. Rebuilder l'image.
- **Admin EasyAdmin** : `public/bundles/` doit être peuplé par `assets:install` au build. Rebuilder l'image.

### Produits absents

La base de données est vide après un premier déploiement. Importer un dump depuis la machine locale :
```bash
# Sur le PC local
mysqldump -u root sports_bottles > dump.sql
scp dump.sql root@157.180.35.139:/home/deploy/sports_bottles/

# Sur le serveur
MYSQL_ROOT_PASSWORD=$(grep MYSQL_ROOT_PASSWORD .env.local | cut -d'=' -f2)
docker compose -f docker-compose.prod.yml exec -T db mysql -uroot -p"$MYSQL_ROOT_PASSWORD" sports_bottles < dump.sql
```

### Images produits absentes

Le volume `products_images` est vide au premier déploiement. Copier depuis le PC local :
```bash
# Sur le PC local
scp -r public/products_images/* root@157.180.35.139:/tmp/products_images/

# Sur le serveur
docker cp /tmp/products_images/. sports_bottles_app:/var/www/html/public/products_images/
docker compose -f docker-compose.prod.yml exec app chown -R www-data:www-data /var/www/html/public/products_images
```
