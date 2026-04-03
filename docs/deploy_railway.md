# Déploiement sur Railway — Sports Bottles

## Résumé de l'audit Docker

| Point de contrôle | Statut | Action |
|---|---|---|
| Image de base (`php:8.4-fpm-alpine`) | ✅ OK | — |
| Liaison PORT dynamique | ✅ Corrigé | `entrypoint.sh` substitue `$PORT` dans la config nginx |
| Instruction `EXPOSE` | ✅ Présent | `EXPOSE 80` (port par défaut local) |
| `CMD` / `ENTRYPOINT` | ✅ Corrigé | `ENTRYPOINT ["/entrypoint.sh"]` démarre nginx + php-fpm |
| Dépendances docker-compose | ✅ Aucune dans Dockerfile | Railway ignore `docker-compose.yml` |
| `.env` exclu du build | ✅ Corrigé | Ajouté dans `.dockerignore` |
| Nom de variable Stripe | ⚠️ Incohérence | `.env` avait `STRIPE_PUBLISHABLE_KEY` (inutilisé) — le code utilise `STRIPE_PUBLIC_KEY` — à corriger dans Railway |

---

## Modifications apportées

### 1. `docker/nginx/default.conf.template` *(nouveau)*
Remplace `docker/nginx/default.conf` statique.
```
listen ${PORT};   ← était listen 80; (hardcodé)
```

### 2. `docker/entrypoint.sh` *(nouveau)*
Script exécuté au démarrage du conteneur :
```sh
export PORT="${PORT:-80}"
envsubst '${PORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
```

### 3. `Dockerfile`
- `gettext` ajouté dans `apk` (fournit `envsubst`)
- Copie du template au lieu du fichier statique
- Copie + `chmod +x` de `entrypoint.sh`
- `CMD` remplacé par `ENTRYPOINT ["/entrypoint.sh"]`

### 4. `.dockerignore`
Ajout des lignes :
```
.env
.env.local
.env.local.php
.env.*.local
```

---

## Variables d'environnement à configurer dans Railway

> **Railway Dashboard → votre service → onglet Variables**
> Ne jamais mettre de secrets dans un fichier commité.

| Variable | Valeur exemple | Obligatoire |
|---|---|---|
| `APP_ENV` | `prod` | ✅ |
| `APP_SECRET` | `<32 caractères hex aléatoires>` | ✅ |
| `APP_DEBUG` | `0` | ✅ |
| `DATABASE_URL` | `mysql://user:pass@host:3306/db?serverVersion=8.0.30&charset=utf8mb4` | ✅ |
| `MESSENGER_TRANSPORT_DSN` | `doctrine://default?auto_setup=0` | ✅ |
| `MAILER_DSN` | `smtp://user:pass@sandbox.smtp.mailtrap.io:2525` | ✅ |
| `STRIPE_SECRET_KEY` | `sk_live_…` ou `sk_test_…` | ✅ |
| `STRIPE_PUBLIC_KEY` | `pk_live_…` ou `pk_test_…` | ✅ |
| `STRIPE_WEBHOOK_SECRET` | `whsec_…` | ✅ |
| `DEFAULT_URI` | `https://votre-app.railway.app` | Recommandé |
| `APP_SHARE_DIR` | `var/share` | Optionnel |

> **Générer APP_SECRET :** `openssl rand -hex 32`

> ⚠️ La variable dans `.env` se nomme `STRIPE_PUBLISHABLE_KEY` (inutilisée). Le code
> utilise `STRIPE_PUBLIC_KEY`. Utilisez **`STRIPE_PUBLIC_KEY`** dans Railway.

---

## Étapes de déploiement via GitHub (sans CLI)

### Pré-requis
- Compte Railway : [railway.app](https://railway.app)
- Repo GitHub avec le code pushé (branche `master` ou `main`)
- Plugin MySQL Railway créé (voir ci-dessous)

---

### Étape 1 — Pousser les modifications sur GitHub

```bash
git add Dockerfile docker/entrypoint.sh docker/nginx/default.conf.template .dockerignore
git commit -m "feat: Railway compatibility — dynamic PORT binding via entrypoint"
git push origin master
```

---

### Étape 2 — Créer un projet Railway

1. Aller sur [railway.app](https://railway.app) → **New Project**
2. Choisir **Deploy from GitHub repo**
3. Autoriser Railway à accéder à GitHub si ce n'est pas déjà fait
4. Sélectionner le repo `sports_bottles`
5. Railway détecte automatiquement le `Dockerfile` et propose un service

---

### Étape 3 — Ajouter le plugin MySQL

1. Dans le projet Railway → **New** → **Database** → **Add MySQL**
2. Railway crée un service MySQL et expose automatiquement la variable `MYSQL_URL`
3. Aller dans l'onglet **Variables** du plugin MySQL → copier la valeur `MYSQL_URL`
4. Reformater en URL Symfony :
   ```
   mysql://user:password@host:3306/railway?serverVersion=8.0.30&charset=utf8mb4
   ```
5. Utiliser cette valeur pour `DATABASE_URL` dans votre service app (étape 4)

---

### Étape 4 — Configurer les variables d'environnement

1. Cliquer sur le service **app** (votre Symfony)
2. Onglet **Variables** → **Add Variable** pour chaque entrée du tableau ci-dessus
3. Coller les valeurs une par une
4. Railway redéploie automatiquement après chaque changement de variable (ou annuler et tout sauvegarder d'un coup avec **Deploy**)

---

### Étape 5 — Configurer le domaine public

1. Onglet **Settings** du service → section **Networking**
2. Cliquer **Generate Domain** → Railway fournit un domaine `*.railway.app`
3. Copier ce domaine et le définir dans la variable `DEFAULT_URI` (important pour Symfony routing)
4. (Optionnel) Configurer un domaine personnalisé dans la même section

---

### Étape 6 — Exécuter les migrations Doctrine

Railway ne lance pas automatiquement les migrations. Deux options :

**Option A — Via le terminal Railway (recommandé pour le premier déploiement)**
1. Service app → onglet **Deploy** → **Terminal** (icône `>_`)
2. Exécuter :
   ```bash
   php bin/console doctrine:migrations:migrate --no-interaction
   php bin/console messenger:setup-transports
   ```

**Option B — Ajouter une commande de release dans `railway.toml`**

Créer (ou mettre à jour) `railway.toml` à la racine du projet :
```toml
[deploy]
releaseCommand = "php bin/console doctrine:migrations:migrate --no-interaction && php bin/console messenger:setup-transports"
```
Railway exécutera cette commande après chaque build réussi, avant de basculer le trafic.

---

### Étape 7 — Configurer le webhook Stripe

1. Aller sur [dashboard.stripe.com](https://dashboard.stripe.com) → **Webhooks**
2. **Add endpoint** → URL : `https://votre-app.railway.app/webhook/stripe`
3. Sélectionner les événements nécessaires (ex. `checkout.session.completed`, `payment_intent.succeeded`)
4. Copier le **Signing secret** (`whsec_…`) → le définir dans `STRIPE_WEBHOOK_SECRET` dans Railway

---

### Étape 8 — Vérifier le déploiement

1. Onglet **Deploy** → consulter les logs de build et de runtime
2. Vérifier que `nginx` et `php-fpm` démarrent sans erreur
3. Ouvrir l'URL publique → la page d'accueil doit s'afficher
4. Tester le tunnel complet : ajout panier → paiement Stripe → confirmation

---

## Points de vigilance post-déploiement

### Stockage des images produits
Les images dans `public/products_images/` sont locales au conteneur et **perdues au redéploiement**.
Solution : connecter un bucket S3 / Cloudflare R2 / Railway Volume et adapter le service de stockage.

### Cache Symfony
Le cache est vidé pendant le build (`cache:clear --no-warmup`). En `APP_ENV=prod` Symfony
régénère automatiquement le cache au premier appel. Un Railway Volume sur `var/cache` peut
accélérer les cold starts.

### Logs
Les logs nginx et php-fpm sont redirigés vers `stdout`/`stderr` → visibles directement dans
l'onglet **Logs** du service Railway.

### Redéploiement automatique
Railway redéploie automatiquement à chaque `git push` sur la branche configurée. Pour désactiver,
aller dans **Settings** → **Deploy** → désactiver **Auto-Deploy**.
