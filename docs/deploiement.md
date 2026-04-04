# Déploiement Sports Bottles sur Hetzner VPS

## Architecture de production

```
Internet
  │  HTTPS 443 / HTTP 80
  ▼
┌─────────────────────────────────────┐
│  VPS Hetzner (Ubuntu 24.04)         │
│                                     │
│  ┌──────────┐   ┌─────────────────┐ │
│  │  Caddy   │──▶│  app (Symfony)  │ │
│  │ SSL auto │   │  nginx+php-fpm  │ │
│  └──────────┘   └────────┬────────┘ │
│                           │         │
│                  ┌────────▼───────┐ │
│                  │   MySQL 8.0    │ │
│                  └────────────────┘ │
└─────────────────────────────────────┘
```

**Caddy** gère automatiquement le certificat SSL Let's Encrypt — aucune manipulation manuelle nécessaire.

---

## Pré-requis

- Compte Hetzner Cloud : [console.hetzner.cloud](https://console.hetzner.cloud)
- Un nom de domaine pointant vers l'IP du VPS (configuré avant le démarrage de Caddy)
- Accès SSH depuis ta machine locale

---

## Étape 1 — Créer le VPS sur Hetzner

1. Va sur [console.hetzner.cloud](https://console.hetzner.cloud) → **New Server**
2. Choisis :
   - **Location** : Nuremberg ou Helsinki (EU) — ou Ashburn (US)
   - **Image** : Ubuntu **24.04**
   - **Type** : `CX22` (2 vCPU, 4 GB RAM) — suffisant pour démarrer
3. **SSH Keys** → clique **Add SSH Key** :
   - Sur ta machine locale, affiche ta clé publique :
     ```bash
     cat ~/.ssh/id_ed25519.pub
     # ou si elle n'existe pas, génère-la :
     ssh-keygen -t ed25519 -C "ton@email.com"
     cat ~/.ssh/id_ed25519.pub
     ```
   - Colle la clé dans Hetzner
4. **Firewall** (optionnel mais recommandé) → crée une règle qui autorise :
   - Port `22` TCP (SSH)
   - Port `80` TCP (HTTP)
   - Port `443` TCP (HTTPS)
5. Clique **Create & Buy** → note l'**adresse IP publique** du serveur

---

## Étape 2 — Pointer ton domaine vers le VPS

Chez ton registrar (OVH, Namecheap, Cloudflare, etc.) :

1. Va dans la gestion DNS de ton domaine
2. Crée (ou modifie) un enregistrement **A** :
   ```
   Type  : A
   Nom   : @  (ou ton-domaine.com)
   Valeur: <IP_DU_VPS>
   TTL   : 3600
   ```
3. Si tu veux le sous-domaine `www` :
   ```
   Type  : CNAME
   Nom   : www
   Valeur: ton-domaine.com
   ```

> ⏳ La propagation DNS prend entre 5 minutes et 2 heures. **Caddy ne peut pas générer le SSL tant que le domaine ne pointe pas vers le VPS.** Vérifie avec : `ping ton-domaine.com`

---

## Étape 3 — Connexion SSH et préparation du serveur

```bash
# Depuis ta machine locale
ssh root@<IP_DU_VPS>
```

### Mise à jour du système
```bash
apt update && apt upgrade -y
```

### Installation de Docker
```bash
curl -fsSL https://get.docker.com | sh
```

### Vérification
```bash
docker --version
docker compose version
```

### Création d'un utilisateur non-root (bonne pratique sécurité)
```bash
adduser deploy
usermod -aG docker deploy
# Copie la clé SSH pour cet utilisateur
rsync --archive --chown=deploy:deploy ~/.ssh /home/deploy
```

---

## Étape 4 — Cloner le projet sur le VPS

```bash
# Passe à l'utilisateur deploy
su - deploy

# Clone le repo GitHub
git clone https://github.com/Rames615/sport_bottles.git /home/deploy/sports_bottles

cd /home/deploy/sports_bottles
```

---

## Étape 5 — Configurer les variables d'environnement

Le fichier `.env.local` ne doit **jamais** être commité. Il faut le créer directement sur le serveur.

```bash
# Copie le template
cp .env.prod.example .env.local

# Édite avec tes vraies valeurs
nano .env.local
```

### Valeurs à renseigner dans `.env.local`

| Variable | Valeur à mettre |
|---|---|
| `APP_SECRET` | `openssl rand -hex 32` (exécute cette commande pour générer) |
| `DEFAULT_URI` | `https://ton-domaine.com` |
| `DATABASE_URL` | `mysql://sports_user:MOT_DE_PASSE@db:3306/sports_bottles?serverVersion=8.0.30&charset=utf8mb4` |
| `MYSQL_ROOT_PASSWORD` | Un mot de passe fort |
| `MYSQL_USER` | `sports_user` |
| `MYSQL_PASSWORD` | Le même mot de passe que dans `DATABASE_URL` |
| `STRIPE_SECRET_KEY` | `sk_live_…` depuis dashboard Stripe |
| `STRIPE_PUBLIC_KEY` | `pk_live_…` depuis dashboard Stripe |
| `STRIPE_WEBHOOK_SECRET` | `whsec_…` depuis dashboard Stripe |
| `MAILER_DSN` | Ton DSN SMTP (Mailtrap, Mailgun, etc.) |

> **Générer APP_SECRET :**
> ```bash
> openssl rand -hex 32
> ```

---

## Étape 6 — Configurer le domaine dans Caddyfile

```bash
nano Caddyfile
```

Remplace `ton-domaine.com` par ton vrai domaine :

```
sportsbottles.com {
    reverse_proxy app:80
}
```

Si tu veux aussi `www` :
```
sportsbottles.com, www.sportsbottles.com {
    reverse_proxy app:80
}
```

---

## Étape 7 — Build et démarrage des conteneurs

```bash
cd /home/deploy/sports_bottles

# Build l'image Symfony et démarre tous les services
docker compose -f docker-compose.prod.yml up -d --build
```

### Vérifier que tout tourne
```bash
docker compose -f docker-compose.prod.yml ps
```

Tu dois voir 3 services `Up` :
```
NAME                    STATUS
sports_bottles_app      Up
sports_bottles_db       Up (healthy)
sports_bottles_caddy    Up
```

---

## Étape 8 — Exécuter les migrations Doctrine

```bash
docker compose -f docker-compose.prod.yml exec app php bin/console doctrine:migrations:migrate --no-interaction
```

Puis les transports Messenger :
```bash
docker compose -f docker-compose.prod.yml exec app php bin/console messenger:setup-transports
```

---

## Étape 9 — Vérifier le SSL et l'application

1. Ouvre `https://ton-domaine.com` dans ton navigateur
2. Le cadenas SSL doit être vert (Caddy a généré le certificat automatiquement)
3. Teste le tunnel complet : accueil → produit → panier → paiement Stripe

---

## Étape 10 — Configurer le webhook Stripe

1. Va sur [dashboard.stripe.com](https://dashboard.stripe.com) → **Développeurs** → **Webhooks**
2. **Ajouter un endpoint** → URL : `https://ton-domaine.com/webhook/stripe`
3. Sélectionne les événements : `checkout.session.completed`, `payment_intent.succeeded`
4. Copie le **Signing secret** (`whsec_…`) → mets à jour `STRIPE_WEBHOOK_SECRET` dans `.env.local`
5. Redémarre l'app :
   ```bash
   docker compose -f docker-compose.prod.yml restart app
   ```

---

## Commandes utiles au quotidien

### Voir les logs en temps réel
```bash
docker compose -f docker-compose.prod.yml logs -f app
docker compose -f docker-compose.prod.yml logs -f caddy
```

### Mettre à jour l'application après un git push
```bash
cd /home/deploy/sports_bottles
git pull origin master
docker compose -f docker-compose.prod.yml up -d --build app
docker compose -f docker-compose.prod.yml exec app php bin/console doctrine:migrations:migrate --no-interaction
docker compose -f docker-compose.prod.yml exec app php bin/console cache:clear
```

### Entrer dans le conteneur app (équivalent SSH)
```bash
docker compose -f docker-compose.prod.yml exec app sh
```

### Redémarrer un service
```bash
docker compose -f docker-compose.prod.yml restart app
docker compose -f docker-compose.prod.yml restart db
```

### Arrêter tous les services
```bash
docker compose -f docker-compose.prod.yml down
```

### Voir l'utilisation des ressources
```bash
docker stats
```

---

## Structure des fichiers de déploiement

```
sports_bottles/
├── Dockerfile                   ← image Symfony (nginx + php-fpm)
├── docker-compose.yml           ← environnement LOCAL uniquement
├── docker-compose.prod.yml      ← environnement PRODUCTION Hetzner
├── Caddyfile                    ← config SSL + reverse proxy
├── .env.prod.example            ← template des variables (commité)
├── .env.local                   ← vraies valeurs (NON commité, sur le serveur)
└── docker/
    ├── nginx/default.conf       ← config nginx interne
    └── supervisord.conf         ← démarre nginx + php-fpm
```

---

## Points de vigilance

### Images produits
Les images uploadées sont stockées dans le volume Docker `products_images` → elles **survivent aux redéploiements**. Pour une solution cloud, envisager S3 / Cloudflare R2.

### Sauvegardes base de données
```bash
# Dump manuel
docker compose -f docker-compose.prod.yml exec db \
  mysqldump -u sports_user -p sports_bottles > backup_$(date +%Y%m%d).sql
```

Planifier une cron job sur le serveur pour automatiser les sauvegardes.

### Renouvellement SSL
Caddy renouvelle les certificats Let's Encrypt **automatiquement** — aucune action requise.

### Firewall UFW (recommandé)
```bash
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
```
