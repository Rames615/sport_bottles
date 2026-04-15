# Base de données via Docker

## Architecture

Le projet utilise **3 conteneurs Docker** définis dans `docker-compose.yml` :

| Service      | Conteneur              | Image                        | Port exposé     | Rôle                          |
|-------------|------------------------|------------------------------|-----------------|-------------------------------|
| `app`       | sports_bottles_app     | PHP 8.3 Apache (Dockerfile)  | `8080` → 80    | Application Symfony           |
| `db`        | sports_bottles_db      | mysql:8.0.30                 | `3307` → 3306  | Base de données MySQL         |
| `phpmyadmin`| sports_bottles_pma     | phpmyadmin/phpmyadmin:latest | `8081` → 80    | Interface web pour la BDD     |

### Schéma réseau

```
┌─────────────────────────────────────────────────────┐
│  Réseau Docker : sports                             │
│                                                     │
│  ┌─────────┐       ┌─────────┐      ┌───────────┐  │
│  │   app   │──────▶│   db    │◀─────│phpmyadmin │  │
│  │ :80     │       │ :3306   │      │ :80       │  │
│  └────┬────┘       └────┬────┘      └─────┬─────┘  │
│       │                 │                  │        │
└───────┼─────────────────┼──────────────────┼────────┘
        │                 │                  │
   Port 8080         Port 3307          Port 8081
        │                 │                  │
┌───────┴─────────────────┴──────────────────┴────────┐
│                    Hôte (Windows)                    │
└─────────────────────────────────────────────────────┘
```

## Démarrage

```bash
# Lancer tous les services (app + db + phpmyadmin)
docker compose up -d

# Vérifier que tout fonctionne
docker compose ps
```

Attendez que le healthcheck de `db` passe à `healthy` (≈ 15-30 secondes) :
```bash
docker inspect --format='{{.State.Health.Status}}' sports_bottles_db
# Résultat attendu : healthy
```

## Accéder à la base de données

### ✅ Via phpMyAdmin (navigateur web)

C'est la méthode la plus simple. Ouvrez votre navigateur :

```
http://localhost:8081
```

- **Serveur** : pré-configuré (db)
- **Utilisateur** : `root`
- **Mot de passe** : `root`
- **Base** : `sports_bottles`

### ✅ Via un client MySQL en ligne de commande

Depuis votre machine hôte (Windows) :
```bash
mysql -h 127.0.0.1 -P 3307 -u root -proot sports_bottles
```

Depuis l'intérieur du conteneur `app` :
```bash
docker exec -it sports_bottles_app bash
mysql -h db -u root -proot sports_bottles
```

### ✅ Via un client graphique (DBeaver, MySQL Workbench, HeidiSQL...)

| Paramètre      | Valeur         |
|----------------|----------------|
| Hôte           | `127.0.0.1`   |
| Port           | `3307`         |
| Utilisateur    | `root`         |
| Mot de passe   | `root`         |
| Base de données| `sports_bottles` |

### ❌ Ce qui NE fonctionne PAS

```
http://127.0.0.1:3307   ← ERREUR : "Got packets out of order"
http://localhost:3307    ← ERREUR : même problème
```

**Pourquoi ?** MySQL utilise un protocole binaire, pas HTTP. Un navigateur web
envoie une requête HTTP (`GET / HTTP/1.1`), mais MySQL attend un paquet
d'authentification binaire. Le serveur MySQL ne comprend pas la requête HTTP et
renvoie une erreur « packets out of order ».

**Solution** : utilisez `http://localhost:8081` (phpMyAdmin) pour accéder à la
BDD via votre navigateur, ou un client MySQL dédié sur le port `3307`.

## Configuration Symfony

### En mode Docker (conteneur `app`)

Le conteneur `app` communique directement avec `db` via le réseau Docker interne.
La variable `DATABASE_URL` est définie dans `docker-compose.yml` :

```
DATABASE_URL="mysql://root:root@db:3306/sports_bottles?serverVersion=8.0.30&charset=utf8mb4"
```

`db` est le nom du service Docker — il est résolu automatiquement en adresse IP interne.

### En mode Laragon (hôte Windows, sans Docker)

Si vous développez directement avec Laragon (sans Docker), modifiez `DATABASE_URL`
dans `.env.local` :

```
DATABASE_URL="mysql://root:@127.0.0.1:3306/sports_bottles?serverVersion=8.0.30&charset=utf8mb4"
```

> **Note** : Laragon utilise `root` sans mot de passe par défaut et le port `3306`.

### En mode hybride (Laragon + DB Docker)

Si vous voulez utiliser Laragon pour PHP mais la base de données Docker :

```
DATABASE_URL="mysql://root:root@127.0.0.1:3307/sports_bottles?serverVersion=8.0.30&charset=utf8mb4"
```

> Ici on utilise le port `3307` (port exposé sur l'hôte) et le mot de passe `root`.

## Migrations

Une fois la base de données accessible :

```bash
# Depuis le conteneur app
docker exec -it sports_bottles_app php bin/console doctrine:migrations:migrate

# Ou depuis Laragon (si PHP disponible en local)
php bin/console doctrine:migrations:migrate
```

## Fixtures (données de test)

```bash
docker exec -it sports_bottles_app php bin/console doctrine:fixtures:load
```

## Commandes utiles

```bash
# Voir les logs de la base de données
docker compose logs db

# Redémarrer uniquement la base de données
docker compose restart db

# Supprimer la base et repartir de zéro
docker compose down -v   # ⚠️ supprime le volume db_data
docker compose up -d

# Vérifier la connexion depuis le conteneur app
docker exec -it sports_bottles_app php bin/console doctrine:database:create --if-not-exists
```

## Résumé des URLs

| Service       | URL / Commande                          | Protocole |
|---------------|-----------------------------------------|-----------|
| Application   | `http://localhost:8080`                 | HTTP      |
| phpMyAdmin    | `http://localhost:8081`                 | HTTP      |
| MySQL (hôte)  | `mysql -h 127.0.0.1 -P 3307 -u root -proot` | MySQL binary |
| MySQL (Docker)| `mysql -h db -P 3306 -u root -proot`   | MySQL binary |
