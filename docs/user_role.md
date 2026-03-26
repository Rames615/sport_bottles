# Gestion des rôles utilisateurs

## Rôles disponibles

| Rôle | Description | Accès |
|------|------------|-------|
| `ROLE_USER` | Attribué automatiquement à tous les utilisateurs | `/profile`, panier, checkout |
| `ROLE_ADMIN` | Administrateur du site | `/admin/*` |

## Utilisateur administrateur par défaut

Les fixtures créent automatiquement un compte administrateur :

| Champ | Valeur |
|-------|--------|
| Email | `sports@bottles` |
| Mot de passe | `123456` |
| Rôle | `ROLE_ADMIN` |
| Vérifié | Oui |

### Chargement via les fixtures

```bash
# Dans Docker
docker compose exec app php bin/console doctrine:fixtures:load --no-interaction

# En local (Laragon)
php bin/console doctrine:fixtures:load --no-interaction
```

> **Attention** : cette commande purge la base de données avant de recharger les données.

## Promouvoir un utilisateur existant

Une commande Symfony dédiée permet de promouvoir n'importe quel utilisateur au rôle `ROLE_ADMIN` sans toucher à la base de données manuellement :

```bash
# Dans Docker
docker compose exec app php bin/console app:user:promote-admin <email>

# En local
php bin/console app:user:promote-admin <email>
```

**Exemple :**

```bash
docker compose exec app php bin/console app:user:promote-admin sports@bottles
```

## Configuration de la sécurité

Les règles d'accès sont définies dans `config/packages/security.yaml` :

```yaml
access_control:
    - { path: ^/admin, roles: ROLE_ADMIN }
    - { path: ^/profile, roles: ROLE_USER }
```

- `/admin/*` — nécessite `ROLE_ADMIN`
- `/profile/*` — nécessite `ROLE_USER` (attribué automatiquement)
- Toutes les autres routes — accessibles sans authentification

## Architecture technique

- **Entité** : `src/Entity/User.php` — champ `roles` (JSON array), `getRoles()` ajoute toujours `ROLE_USER`
- **Fixtures** : `src/DataFixtures/AppFixtures.php` — crée l'admin avec mot de passe hashé via `UserPasswordHasherInterface`
- **Commande** : `src/Command/PromoteUserAdminCommand.php` — promotion par email
- **Hashage** : algorithme `auto` (bcrypt) configuré dans `security.yaml`
