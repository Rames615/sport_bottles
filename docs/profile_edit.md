# Modification du Profil Utilisateur - Documentation

## Vue d'ensemble

La fonctionnalité de modification du profil utilisateur permet aux utilisateurs authentifiés de consulter et modifier les informations de leur compte à l'adresse `/mon-profil` et `/mon-profil/editer`.

---

## Flux de fonctionnement

```
┌─────────────────────────────────────────────────────────────────────┐
│                    PROCESSUS DE MODIFICATION DE PROFIL              │
└─────────────────────────────────────────────────────────────────────┘

ÉTAPE 1: Consultation du profil
────────────────────────────────
   Utilisateur connecté
       ↓
   Clique sur "Mon Profil" (dans la navigation ou menu)
       ↓
   Route: GET /mon-profil
       ↓
   AccountController::profile()
       ↓
   Affichage de las page:
   ┌───────────────────────────────────┐
   │ • Informations du compte          │
   │   - Email                         │
   │   - Statut (Vérifié/Non vérifié) │
   │   - Rôle (Admin/User)             │
   │   - Bouton "Éditer"               │
   ├───────────────────────────────────┤
   │ • Adresses de livraison           │
   │   (si enregistrées)               │
   ├───────────────────────────────────┤
   │ • Boutons d'action                │
   │   - Mes Commandes                 │
   │   - Continuer mes achats          │
   └───────────────────────────────────┘


ÉTAPE 2: Clic sur le bouton "Éditer"
─────────────────────────────────────
   Utilisateur sur page /mon-profil
       ↓
   Clique bouton "Éditer"
       ↓
   Route: GET /mon-profil/editer
       ↓
   AccountController::editProfile()
       ↓
   Affichage formulaire d'édition:
   ┌───────────────────────────────────┐
   │ ÉDITER MON PROFIL                 │
   ├───────────────────────────────────┤
   │ Champ: Email                      │
   │ [votre@email.com              ]   │
   │                                   │
   │ [Enregistrer] [Annuler]           │
   │                                   │
   │ Info: Seul l'email peut être      │
   │       modifié. Pour le mot de     │
   │       passe, utilisez la          │
   │       réinitialisation.           │
   └───────────────────────────────────┘


ÉTAPE 3: Modification de l'email
─────────────────────────────────
   Utilisateur modifie le champ email
       ↓
   Exemple: "nouveau@email.com"
       ↓
   Clique "Enregistrer"
       ↓
   Soumission POST à /mon-profil/editer
       ↓
   Validations:
   ┌───────────────────────────────────┐
   │ 1. Email n'est pas vide           │
   │ 2. Format email valide            │
   │ 3. Email unique en BD              │
   │    (sauf le sien actuel)          │
   └───────────────────────────────────┘
       ↓
       ┌─ Erreur de validation:
       │  - Afficher messages d'erreur
       │  - Réafficher le formulaire
       │
       └─ Validation réussie:
           ↓
           User::setEmail(nouveau@email.com)
           ↓
           EntityManager::flush()
           ↓ (Persiste en BD)
           ↓
   Flash succès: "Profil mis à jour"
       ↓
   Redirection: GET /mon-profil


ÉTAPE 4: Retour à la page de profil
────────────────────────────────────
   Page /mon-profil s'affiche
       ↓
   Affichage flash succès
       ↓
   Nouvel email visible:
   - E-mail: nouveau@email.com


ÉTAPE 5: Annulation (optionnel)
────────────────────────────────
   Utilisateur sur /mon-profil/editer
       ↓
   Clique "Annuler"
       ↓
   Redirection: GET /mon-profil
       ↓ (aucune modification sauvegardée)
```

---

## Architecture et Composants

### 1. **ProfileFormType** (`src/Form/ProfileFormType.php`)

Formulaire d'édition du profil utilisateur.

**Champs:**

| Champ | Type | Validations | Placeholder |
|-------|------|------------|-------------|
| `email` | EmailType | NotBlank, Email | `votre@email.com` |

**Caractéristiques:**
- Mappé à l'entité `User` (`data_class: User::class`)
- Validation d'unicité déjà gérée par l'entité (contrainte `UniqueEntity`)
- Attribut `autocomplete="email"` pour les navigateurs

---

### 2. **AccountController** (`src/Controller/AccountController.php`)

Contrôleur gérant le profil utilisateur.

#### Route 1: `profile()` — `/mon-profil`

```php
#[Route('/mon-profil', name: 'app_profile')]
public function profile(): Response
{
    return $this->render('account/profile.html.twig');
}
```

**Logique:**
- Requiert authentification (`#[IsGranted('ROLE_USER')]` sur la classe)
- Rend template affichant les infos du compte courant
- `app.user` disponible automatiquement (Twig)

#### Route 2: `editProfile(token)` — `/mon-profil/editer`

```php
#[Route('/mon-profil/editer', name: 'app_profile_edit')]
public function editProfile(Request $request, EntityManagerInterface $em): Response
```

**Logique:**

1. GET `/mon-profil/editer`:
   - Création forme avec User courant pré-remplie
   - Affichage du formulaire

2. POST `/mon-profil/editer`:
   - Liaison request au formulaire
   - Validation des données
   - Si valide:
     - `$em->flush()` (persiste modifications)
     - Flash succès
     - Redirection `/mon-profil`
   - Si invalide:
     - Réaffichage formulaire avec erreurs

---

### 3. **Templates Twig**

#### `templates/account/profile.html.twig`

**Structure:**

```
┌─────────────────────────────────────┐
│ Mon Profil (titre + icône)          │
├─────────────────────────────────────┤
│ Messages flash (succès/erreur)      │
├─────────────────────────────────────┤
│ CARTE: Informations du compte       │
│ ┌─────────────────────────────────┐ │
│ │ Email: user@example.com         │ │
│ │ Statut: Vérifié ✓               │ │
│ │ Rôle: Utilisateur               │ │
│ │ Bouton "Éditer" (coin)          │ │
│ └─────────────────────────────────┘ │
├─────────────────────────────────────┤
│ CARTE: Adresses de livraison        │
│ (affichage seulement)               │
├─────────────────────────────────────┤
│ Boutons d'action:                   │
│ - Mes Commandes                     │
│ - Continuer mes achats              │
└─────────────────────────────────────┘
```

**Éléments principaux:**

| Élément | Description |
|---|---|
| Titre H1 | "Mon Profil" avec icône utilisateur |
| Messages flash | Succès (vert) et erreur (rouge) rejetable |
| Carte infos | Email, statut, rôle + bouton éditer |
| Bouton "Éditer" | Lien vers `/mon-profil/editer` |
| Carte adresses | Liste des adresses de livraison |
| Boutons action | Liens vers commandes et catalogue |

**Classes Bootstrap utilisées:**
- `.container`, `.row`, `.col-lg-8` — layout responsive
- `.card`, `.card-header`, `.card-body` — cartes
- `.bg-dark`, `.text-white` — header styling
- `.badge.bg-success`, `.bg-warning` — badges de statut
- `.d-flex.justify-content-between` — layout header (bouton "Éditer" en haut-droit)
- `.btn.btn-outline-dark` — boutons d'action
- `.alert.alert-success`, `.alert-danger` — messages flash

---

#### `templates/account/edit_profile.html.twig`

**Structure:**

```
┌─────────────────────────────────────┐
│ Éditer mon Profil (titre + icône)   │
├─────────────────────────────────────┤
│ Messages flash (succès/erreur)      │
├─────────────────────────────────────┤
│ CARTE: Modifier vos informations    │
│ ┌─────────────────────────────────┐ │
│ │ <form method="post">            │ │
│ │                                 │ │
│ │ Label: Email *                  │ │
│ │ [votre@email.com            ]   │ │
│ │ (messages d'erreur si invalid)  │ │
│ │                                 │ │
│ │ [Enregistrer] [Annuler]         │ │
│ │                                 │ │
│ │ </form>                         │ │
│ └─────────────────────────────────┘ │
├─────────────────────────────────────┤
│ ALERTE: Informations               │
│ - Seul email modifiable             │
│ - Lien vers réinitialisation PWD    │
└─────────────────────────────────────┘
```

**Éléments principaux:**

| Élément | Description |
|---|---|
| Titre H1 | "Éditer mon Profil" avec icône édition |
| Formulaire | Méthode POST, Bootstrap validation |
| Champ email | Prérempli avec valeur courante |
| Boutons | Enregistrer (primary) et Annuler (outline) |
| Alerte info | Explique limitations et alternatives |

**Classes Bootstrap utilisées:**
- `.needs-validation` — Bootstrap form validation styling
- `.form-label.fw-semibold` — labels gras
- `.form-control`, `.is-invalid` — champs d'entrée
- `.invalid-feedback.d-block` — messages d'erreur
- `.btn.btn-primary`, `.btn.btn-outline-secondary` — boutons
- `.alert.alert-info` — boîte d'information
- `.d-flex.gap-2` — espacement boutons

---

## Sécurité

### Principes implémentés:

| Principe | Implémentation |
|---|---|
| **Authentification requise** | Attribut `#[IsGranted('ROLE_USER')]` sur la classe |
| **Validation email** | Contraintes Symfony: `Email`, `NotBlank`, `UniqueEntity` |
| **CSRF protection** | Token CSRF automatique dans le formulaire |
| **Unicité email** | Contrainte `@UniqueEntity(['email'])` sur User |
| **Affichage sécurisé** | Seul `app.user` (compte courant) peut être modifié |

### Cas d'usage malveillant bloqués:

**Tentative 1: Accès anonyme**
```
GET /mon-profil → Redirection /login
POST /mon-profil/editer → Redirection /login
```

**Tentative 2: Email déjà utilisé**
```
POST /mon-profil/editer
- Email: autre.utilisateur@example.com

Résultat: Message d'erreur, formulaire réaffichée
```

**Tentative 3: Email invalide**
```
POST /mon-profil/editer
- Email: "pas-une-adresse-email"

Résultat: "Veuillez entrer une adresse email valide."
```

---

## Validation des données

### Email - Validations

```
┌──────────────────────┐
│ Input utilisateur    │ → [Vérifications]
└──────────────────────┘

1. NotBlank
   ├─ Message: "Veuillez entrer votre adresse email."
   └─ Vérifie: Champ non vide

2. Email
   ├─ Message: "Veuillez entrer une adresse email valide."
   └─ Vérifie: Format email valide

3. UniqueEntity (sur User entity)
   ├─ Champ: email
   ├─ Message: "There is already an account with this email"
   ├─ Ignorance: L'email courant de l'utilisateur
   └─ Vérifie: Email unique en BD (sauf compte courant)

   ↓

   Toutes réussies → Formulaire valide
                  → flush() → Modifications sauvegardées
                  → Redirect /mon-profil

   Au moins une échouée → Erreurs affichées
                       → Formulaire réaffichée
                       → Aucune modification
```

---

## Cycle de vie de la requête

### GET /mon-profil

```
Browser Request
    ↓
Symfony Router
    ↓
SecurityListener (valider authentification)
    ├─ Non authentifié? → Redirection /login
    └─ Authentifié? → Continuer
    ↓
AccountController::profile()
    ↓
$this->getUser() → Récupère User courant
    ↓
$this->render('account/profile.html.twig')
    ├─ Context: app.user disponible
    └─ Affiche informations
    ↓
Response HTTP 200
```

### POST /mon-profil/editer

```
Form Submission (POST)
    ↓
Symfony Router → AccountController::editProfile()
    ↓
$form->handleRequest($request)
    ├─ Lie données POST
    ├─ Valide contraintes
    ↓
    ┌─ form->isSubmitted() && form->isValid()
    │   ├─ OUI:
    │   │  ├─ $em->flush() (INSERT/UPDATE)
    │   │  ├─ addFlash('success', '...')
    │   │  └─ redirectToRoute('app_profile')
    │   │
    │   └─ NON:
    │      └─ Réafficher template + erreurs
    ↓
Response HTTP 302 (redirect) ou 200
```

---

## États possibles

### Après modification réussie

```
BD avant               BD après
┌──────────┐         ┌──────────┐
│ User {   │         │ User {   │
│   email: │         │   email: │
│   "old@" │    →    │   "new@" │
│ }        │         │ }        │
└──────────┘         └──────────┘
      ↓                    ↓
  Affichage            Affichage
  "old@example.com"    "new@example.com"
```

### Après modification échouée

```
Tentative: changement email vers "deja@utilise.com"

Résultat:
┌─ BD: Aucun changement
│  (email reste l'ancien)
│
└─ Browser: Message erreur
   "There is already an account with this email"
   Formulaire: champ pré-rempli avec l'email saisi
```

---

## Fichiers impliqués

```
src/
├── Controller/
│   └── AccountController.php        (routes + logique)
└── Form/
    └── ProfileFormType.php          (validation + champs)

templates/account/
├── profile.html.twig                (affichage du profil)
└── edit_profile.html.twig           (formulaire d'édition)
```

---

## Flux de données

```
┌─────────────────────────────────────────┐
│         USER → BROWSER                  │
├─────────────────────────────────────────┤
│ 1. Clique "Mon Profil"                  │
│    → GET /mon-profil                    │
│    ← render profile.html.twig           │
│                                         │
│ 2. Clique "Éditer"                      │
│    → GET /mon-profil/editer             │
│    ← render edit_profile.html.twig      │
│       (form vide ou pré-rempli)         │
│                                         │
│ 3. Saisit nouvel email + "Enregistrer"  │
│    → POST /mon-profil/editer            │
│    ← Validations + BD flush             │
│    ← Flash + Redirect                   │
│    ← render profile.html.twig           │
│       (affiche nouvel email)            │
└─────────────────────────────────────────┘
```

---

## Test manuel

### Scénario 1: Modification réussie

1. **Accès au profil:**
   ```
   GET https://localhost:8000/mon-profil
   → Affichage infos: email, statut, rôle
   ```

2. **Ouverture formulaire:**
   ```
   Clique "Éditer"
   GET https://localhost:8000/mon-profil/editer
   → Formulaire email pré-rempli
   ```

3. **Modification email:**
   ```
   Email actuel: user@example.com
   Nouveau: newuser@example.com
   Clique "Enregistrer"
   \→ POST /mon-profil/editer
   → Validation OK
   → BD flush
   → Flash succès
   → Redirection /mon-profil
   ```

4. **Vérification:**
   ```
   GET /mon-profil
   → Email affiché: newuser@example.com
   → Flash: "Profil mis à jour avec succès"
   ```

### Scénario 2: Email déjà utilisé

1. Utilisateur A: user1@example.com
2. Utilisateur B (connecté): user2@example.com
3. Utilisateur B tente changer vers: user1@example.com
4. POST validation échoue
5. Erreur affichée: "There is already an account with this email"

### Scénario 3: Annulation

1. GET /mon-profil/editer
2. Clique "Annuler"
3. GET /mon-profil (aucune modification)

---

## Routes

| Route | Méthode | Description |
|---|---|---|
| `app_profile` | GET/POST | Affichage profil (GET) + redirection (POST) |
| `app_profile_edit` | GET/POST | Affichage formulaire (GET), traitement (POST) |

---

## Résumé

La modification de profil est une fonctionnalité simple mais sécurisée qui permet aux utilisateurs de changer leur adresse email. Le formulaire est validé côté client et serveur, et l'unicité est vérifiée en BD. Les messages flash informent l'utilisateur du succès ou des erreurs.
