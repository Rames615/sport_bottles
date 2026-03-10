# Password Reset Feature - Documentation complète

## Vue d'ensemble

La fonctionnalité de réinitialisation de mot de passe permet aux utilisateurs qui ont oublié leur mot de passe de recréer un accès à leur compte en toute sécurité. Le système fonctionne entièrement avec des tokens sécurisés (256-bit) générés aléatoirement, valides pendant 1 heure seulement.

---

## Flux de fonctionnement general

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    PROCESSUS DE RÉINITIALISATION                        │
└─────────────────────────────────────────────────────────────────────────┘

ÉTAPE 1: Accès au formulaire
─────────────────────────────
   Utilisateur
       ↓
   Clique sur "Mot de passe oublié ?"
       ↓    (sur la page de login)
   Route: /mot-de-passe-oublie
       ↓
   ResetPasswordController::forgotPassword()
       ↓
   Affichage du formulaire (ForgotPasswordFormType)


ÉTAPE 2: Soumission de l'email
──────────────────────────────
   Utilisateur saisit son email
       ↓
   Clique "Envoyer le lien"
       ↓
   Soumission POST à /mot-de-passe-oublie
       ↓
   ResetPasswordController::forgotPassword()
       ↓
       ┌─────────────────────────────────────┐
       │ Vérifications:                      │
       ├─────────────────────────────────────┤
       │ 1. Email est valide?                │
       │ 2. Email existe dans la BDD?        │
       │ 3. Utiliser trouvé?                 │
       └─────────────────────────────────────┘
       ↓
       ┌─ Non: Message succès (même message) ← Sécurité!
       │
       └─ Oui: PasswordService::generateResetToken()
           ↓
           ┌─────────────────────────────────────┐
           │ Génération du token:                │
           ├─────────────────────────────────────┤
           │ - bin2hex(random_bytes(32))         │
           │ - Résultat: 64 caractères hex      │
           │ - Stockage: User.resetToken        │
           │ - Expiration: maintenant + 1h      │
           │ - Sauvegarde en BD (flush)          │
           └─────────────────────────────────────┘
           ↓
           PasswordService::sendResetEmail()
           ↓
           ┌─────────────────────────────────────┐
           │ Email envoyé (Mailtrap):            │
           ├─────────────────────────────────────┤
           │ De: no-reply@sportsbottles.fr       │
           │ Sujet: "Réinitialisation..."        │
           │ Template: emails/reset_password/    │
           │ - reset.html.twig (HTML)            │
           │ - reset.txt.twig (plain text)       │
           │ Contient: lien avec token           │
           │ Délai: 1 heure (TOKEN_LIFETIME)     │
           └─────────────────────────────────────┘
           ↓
   Flash message: "Si un compte existe..."
       ↓
   Redirection à /mot-de-passe-oublie


ÉTAPE 3: Clic sur le lien dans l'email
──────────────────────────────────────
   Email reçu par utilisateur
       ↓
   Clique sur: https://localhost/reinitialiser-mot-de-passe/{TOKEN}
       ↓
   Route: /reinitialiser-mot-de-passe/{token}
       ↓
   ResetPasswordController::resetPassword(token)
       ↓
       ┌─────────────────────────────────────┐
       │ Validation du token:                │
       ├─────────────────────────────────────┤
       │ - Token existe en BD?               │
       │ - Compte associé trouvé?            │
       │ - Token expiré?                     │
       └─────────────────────────────────────┘
       ↓
       ┌─ Token invalide/expiré:
       │  Flash erreur
       │  Redirection: /mot-de-passe-oublie
       │
       └─ Token valide:
           ↓
           Affichage formulaire de réinitialisation
           (ResetPasswordFormType)
           Contient:
           - Nouveau mot de passe (champ 1)
           - Confirmation mot de passe (champ 2)
           - Validation: min 6 caractères


ÉTAPE 4: Soumission du nouveau mot de passe
────────────────────────────────────────────
   Utilisateur saisit nouveau mot de passe
       ↓
   Confirm le mot de passe (même valeur)
       ↓
   Clique "Réinitialiser le mot de passe"
       ↓
   Soumission POST
       ↓
   Validation des contraintes
       ↓
       ┌─ Erreur: Affichage messages d'erreur (réutiliser la form)
       │
       └─ OK:
           ↓
           UserPasswordHasherInterface::hashPassword()
           ↓ (Hachage du nouveau mot de passe)
           ↓
           User::setPassword(encrypted_password)
           ↓
           PasswordService::clearToken()
           ├─ User.resetToken = NULL
           ├─ User.resetTokenExpiresAt = NULL
           └─ Flush en BD (token détruit)
           ↓
   Flash succès: "Mot de passe réinitialisé"
       ↓
   Redirection à /login


ÉTAPE 5: Connexion avec nouveau mot de passe
─────────────────────────────────────────────
   Utilisateur saisit email et nouveau mot de passe
       ↓
   Clique "Se connecter"
       ↓
   Authentification réussit
       ↓
   Utilisateur connecté ✓
```

---

## Architecture et Composants

### 1. **User Entity** (`src/Entity/User.php`)

Deux nouveaux champs ajoutés:

```php
#[ORM\Column(length: 100, nullable: true)]
private ?string $resetToken = null;

#[ORM\Column(nullable: true)]
private ?\DateTimeImmutable $resetTokenExpiresAt = null;
```

| Champ | Type | Description |
|-------|------|-------------|
| `resetToken` | varchar(100) | Token unique et sécurisé (64 caractères hex) |
| `resetTokenExpiresAt` | datetime | Date/heure d'expiration du token (1h) |

**Getters/Setters:**
- `getResetToken()` / `setResetToken()`
- `getResetTokenExpiresAt()` / `setResetTokenExpiresAt()`

---

### 2. **PasswordService** (`src/Service/PasswordService.php`)

Service dédié gerant toute la logique de réinitialisation.

#### Méthodes principales:

```php
generateResetToken(User $user): string
```
- Génère token via `bin2hex(random_bytes(32))` — 256 bits d'entropie
- Stocke le token + expiration (+ 1h) sur l'utilisateur
- Persiste en base de données
- Retourne le token (utilisé pour générer le lien email)

```php
validateToken(string $token): ?User
```
- Recherche l'utilisateur ayant ce token
- Vérifie si le token n'a pas expiré
- Si expiré: appelle `clearToken()` et retourne null
- Retourne l'utilisateur ou null

```php
clearToken(User $user): void
```
- Nullifie `resetToken` et `resetTokenExpiresAt`
- Persiste les changements
- Utilisé après succès de réinitialisation

```php
sendResetEmail(User $user, string $token): void
```
- Crée une URL complète: `/reinitialiser-mot-de-passe/{token}`
- Envoie email HTML + plain text
- Gestion d'erreur: log + exception `RuntimeException`

#### Dépendances injectées:

| Dépendance | Utilité |
|---|---|
| `EntityManagerInterface` | Persistance en BD (flush) |
| `UserRepository` | Recherche d'utilisateurs par token |
| `MailerInterface` | Envoi des emails |
| `LoggerInterface` | Logging des opérations |
| `UrlGeneratorInterface` | Génération du lien absolu pour l'email |

---

### 3. **Form Types**

#### `ForgotPasswordFormType` (`src/Form/ForgotPasswordFormType.php`)

```php
- Champ: email (EmailType)
- Validations: NotBlank, Email format
- Non mappé à l'entité (sécurité)
- Message d'erreur: "Veuillez entrer une adresse email valide."
```

**Pourquoi non mappé?** Empêche la fuite d'information sur l'existence d'un email dans la BD.

#### `ResetPasswordFormType` (`src/Form/ResetPasswordFormType.php`)

```php
- Champ: plainPassword (RepeatedType)
  - Type: PasswordType (masqué)
  - Confirmation: champ répété
  - Validations:
    * NotBlank: "Veuillez entrer un mot de passe"
    * Length: min 6, max 4096 caractères
  - Message d'erreur: "Les deux mots de passe doivent être identiques"
  - Non mappé (hachage en controller)
```

---

### 4. **Controller** (`src/Controller/ResetPasswordController.php`)

#### Route 1: `forgotPassword()` — `/mot-de-passe-oublie`

**Logique:**

1. Redirection si authentifié (→ `app_home`)
2. Affichage form + traitement POST
3. **Sécurité de base:** Même message succès que l'email existe ou non
4. Si trouvé:
   - `PasswordService::generateResetToken()` → token créé
   - `PasswordService::sendResetEmail()` → email envoyé
5. Flash succès (generic) + redirection

#### Route 2: `resetPassword(token)` — `/reinitialiser-mot-de-passe/{token}`

**Logique:**

1. Redirection si authentifié (→ `app_home`)
2. Validation token:
   - Appel `PasswordService::validateToken(token)`
   - Si invalide/expiré: flash erreur + redirection
3. Affichage form réinitialisation
4. Traitement POST:
   - Validation formulaire
   - Hachage nouveau mot de passe
   - `User::setPassword()`
   - `PasswordService::clearToken()` — token détruit
   - Flash succès → redirection `/login`

---

### 5. **Templates Twig**

#### Pages utilisateur:

**`templates/reset_password/forgot.html.twig`**
- Réutilise CSS classes: `.login-page`, `.login-card`, `.login-header` (cohérence visuelle)
- Formulaire email
- Lien retour à `/login`
- Affichage des flash messages

**`templates/reset_password/reset.html.twig`**
- Même structure visuelle
- Formulaire: 2 champs password (nouveau + confirm)
- Message: "Minimum 6 caractères"
- Affichage des erreurs de validation
- Lien retour à `/login`

#### Emails:

**`templates/emails/reset_password/reset.html.twig`**
- Extends `emails/layout.html.twig` (branding SportBottles)
- CTA button: "Réinitialiser mon mot de passe"
- Affichage date/heure d'expiration
- Fallback URL textuelle

**`templates/emails/reset_password/reset.txt.twig`**
- Plain text fallback
- Même information: URL, expiration
- Supporté par tous les clients email

---

## Sécurité

### Principes de sécurité implémentés:

| Principe | Implémentation |
|---|---|
| **Entropie du token** | `bin2hex(random_bytes(32))` = 256 bits |
| **Expiration du token** | 1 heure (TOKEN_LIFETIME_SECONDS = 3600) |
| **Single-use token** | Token nullifié après reset réussi |
| **No user enumeration** | Même message si email existe ou non |
| **Password hashing** | `UserPasswordHasherInterface` avec algo `auto` |
| **CSRF protection** | Tous les formulaires incluent le token CSRF |
| **Password confirmation** | RepeatedType force l'utilisateur à taper 2x |
| **Authenticated redirect** | Les utilisateurs connectés sont redirigés |

### Scénarios sécurisés:

**Token volé par attaque man-in-the-middle:**
- Token expiré après 1h
- Un seul reset possible par token

**Attaque par brute force (deviner token):**
- 256 bits d'entropie = 2^256 combinaisons possibles
- Pratiquement impossible

**Compromis du lien email:**
- Token expire après 1h
- Utilisateur peut refaire une demande

---

## Flux de la base de données

### Migration appliquée

```sql
ALTER TABLE user ADD
  reset_token VARCHAR(100) DEFAULT NULL,
  reset_token_expires_at DATETIME DEFAULT NULL;
```

### État des champs lors du cycle de vie

| État | reset_token | reset_token_expires_at |
|---|---|---|
| Normal (pas de demande) | NULL | NULL |
| Après demande email | `abc123...def` (64 hex) | `2026-03-10 12:30:00` |
| Jour suivant (token expiré) | `abc123...def` | `2026-03-10 12:30:00` (passé) |
| Après reset réussi | NULL | NULL |

---

## Dépendances et intégration

### Services injectés automatiquement

| Service | Utilisé dans |
|---|---|
| `PasswordService` | `ResetPasswordController` |
| `UserRepository` | `PasswordService`, `ResetPasswordController` |
| `EntityManagerInterface` | `PasswordService` |
| `MailerInterface` | `PasswordService` |
| `LoggerInterface` | `PasswordService` |
| `UrlGeneratorInterface` | `PasswordService` |
| `UserPasswordHasherInterface` | `ResetPasswordController` |

### Routes générées

```
app_forgot_password                    /mot-de-passe-oublie
app_reset_password                     /reinitialiser-mot-de-passe/{token}
```

---

## Gestion des erreurs et cas limites

### Cas 1: Email inexistant

```
Utilisateur: soumis email@inexistant.fr

Résultat:
- Aucune requête BD
- Flash: "Si un compte existe..."
- Aucun email envoyé
- Sécurité: aucune info divulguée
```

### Cas 2: Token expiré

```
Utilisateur: clique lien reçu depuis 2 heures

Résultat:
- ResetPasswordController::resetPassword()
- PasswordService::validateToken() retourne null
- Token automatiquement nullifié en BD
- Flash erreur: "Ce lien...a expiré"
- Redirection: /mot-de-passe-oublie
```

### Cas 3: Token invalide (forgé)

```
Utilisateur: modifie le token dans l'URL

Résultat:
- findOneBy(['resetToken' => 'fake_token'])
- Retourne null (pas de match)
- PasswordService::validateToken() retourne null
- Flash + redirection comme cas 2
```

### Cas 4: Mots de passe différents

```
Utilisateur: tape deux mots de passe différents

Résultat:
- Validation formulaire échoue
- Message: "Les deux mots de passe doivent être identiques"
- Formulaire réaffichée avec erreur
- Aucun changement en BD
```

### Cas 5: Mot de passe trop court

```
Utilisateur: tape "12345" (5 chars)

Résultat:
- Validation Length échoue
- Message: "Votre mot de passe doit contenir au moins 6 caractères"
- Formulaire réaffichée
```

---

## Logging et monitoring

### Logs en cas de succès

```
[2026-03-10 12:15:30] app.INFO: Password reset email sent {"user_id":42,"to":"user@example.com"}
```

### Logs en cas d'erreur

```
[2026-03-10 12:15:31] app.ERROR: Failed to send password reset email: Too many emails per second. ...
{"exception":"...","user_id":42}
```

---

## Test manuel

### Scénario de test complet

1. **Accès au formulaire:**
   ```
   GET /mot-de-passe-oublie
   → Affichage formulaire email
   ```

2. **Soumission email:**
   ```
   POST /mot-de-passe-oublie
   - Email: toto@example.com
   → Flash succès
   → Vérifier Mailtrap: email reçu
   ```

3. **Clic sur lien email:**
   ```
   GET /reinitialiser-mot-de-passe/a1b2c3d4e5f6...
   → Affichage formulaire nouveau mot de passe
   ```

4. **Soumission nouveau mot de passe:**
   ```
   POST /reinitialiser-mot-de-passe/a1b2c3d4e5f6...
   - plainPassword: "NewPass123"
   - plainPassword (confirm): "NewPass123"
   → Flash succès: "Mot de passe réinitialisé"
   → Redirection /login
   ```

5. **Connexion avec nouveau mot de passe:**
   ```
   POST /login
   - Email: toto@example.com
   - Password: NewPass123
   → Authentification réussie ✓
   ```

6. **Réutilisation du token (sécurité):**
   ```
   GET /reinitialiser-mot-de-passe/a1b2c3d4e5f6... (même token)
   → Flash erreur: "Ce lien... a expiré"
   → Redirection /mot-de-passe-oublie
   ```

---

## Fichiers impliqués

```
src/
├── Entity/User.php                    (resetToken, resetTokenExpiresAt)
├── Service/PasswordService.php         (logique core)
├── Form/
│   ├── ForgotPasswordFormType.php      (email input)
│   └── ResetPasswordFormType.php       (new password + confirm)
└── Controller/
    └── ResetPasswordController.php     (routes + orchestration)

templates/
├── security/
│   └── login.html.twig                (lien "Mot de passe oublié ?")
├── reset_password/
│   ├── forgot.html.twig               (formulaire email)
│   └── reset.html.twig                (formulaire nouveau password)
└── emails/
    └── reset_password/
        ├── reset.html.twig            (email HTML)
        └── reset.txt.twig             (email plain text)

migrations/
└── Version20260310112800.php          (ALTER TABLE user)
```

---

## Résumé des étapes

1. ✅ User Entity: ajout colonnes `resetToken` + `resetTokenExpiresAt`
2. ✅ Migration: création des colonnes en BD
3. ✅ PasswordService: gestion tokens, envoi emails
4. ✅ Form Types: formulaires email + nouveau password
5. ✅ ResetPasswordController: 2 routes (oublié + reset)
6. ✅ Twig Templates: 4 fichiers (2 pages + 2 emails)
7. ✅ Login Page: lien vers formulaire oublié
8. ✅ Cache clear: compilation complète

La fonctionnalité est **prête à l'emploi** ✓
