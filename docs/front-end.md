# 14. Partie Front-End : UX, intégration et logique d’affichage

Cette section présente la conception et le développement de la partie front-end de l’application **Sports Bottles**.  
Elle met en évidence les choix réalisés en matière d’ergonomie (UX), d’intégration visuelle et de logique d’affichage dynamique.

---

## 14.1 Objectifs du Front-End

Le front-end a été conçu pour répondre aux objectifs suivants :

- Offrir une navigation fluide et intuitive
- Mettre en valeur les produits et les promotions
- Garantir une expérience utilisateur cohérente
- Permettre une interaction simple (panier, commande, paiement)
- Assurer un design responsive (desktop, tablette, mobile)

---

## 14.2 Maquettage et ergonomie (UX)

### Principes UX appliqués

- **Hiérarchisation visuelle claire**
  - Mise en avant des produits, prix et promotions
- **Navigation simplifiée**
  - Accès rapide aux pages principales :
    - Accueil
    - Produits
    - Panier
    - Compte
- **Feedback utilisateur immédiat**
  - Confirmation après ajout au panier
  - Messages d’erreur (ex : stock insuffisant)
- **Optimisation du parcours d’achat**
  - Réduction du nombre d’étapes

---

### Pages principales

- Page d’accueil (Home)
- Catalogue produits
- Détail produit
- Panier
- Checkout (adresse + résumé)
- Paiement
- Confirmation de commande
- Espace utilisateur
- Back-office (admin)

---

### 📸 Zone screenshot — Page d’accueil
![Home](screenshots/home.png)

---

### 📸 Zone screenshot — Catalogue produits
![Catalogue](screenshots/catalogue.png)

---

### 📸 Zone screenshot — Détail produit
![Produit](screenshots/product.png)

---

## 14.3 Intégration avec Twig

Le moteur de templates **Twig** est utilisé pour générer dynamiquement les pages HTML.

### Organisation des templates

Les templates sont structurés par domaine fonctionnel :

- `home/`
- `product/`
- `cart/`
- `checkout/`
- `payment/`
- `admin/`
- `security/`

---

### Template principal (layout)

```twig
<!DOCTYPE html>
<html>
<head>
    <title>{% block title %}Sports Bottles{% endblock %}</title>
</head>
<body>
    {% include 'partials/navbar.html.twig' %}
    
    <main>
        {% block body %}{% endblock %}
    </main>
</body>
</html>
```
👉 Permet :

Une cohérence visuelle globale

Une réutilisation du code

Une maintenance facilitée

14.4 Affichage dynamique des données
Boucle d’affichage des produits
{% for product in products %}
    <div class="product-card">
        <h3>{{ product.designation }}</h3>
        <p>{{ product.price }} €</p>
        <a href="{{ path('product_show', {id: product.id}) }}">
            Voir le produit
        </a>
    </div>
{% endfor %}

👉 Permet :

Affichage dynamique depuis la base de données

Navigation vers le détail produit

📸 Zone screenshot — Cards produits

14.5 Logique d’affichage conditionnelle
Utilisateur connecté
{% if app.user %}
    <span>Bienvenue {{ app.user.userIdentifier }}</span>
{% else %}
    <a href="{{ path('app_login') }}">Connexion</a>
{% endif %}
Badge du panier
{% if cartCount > 0 %}
    <span class="badge">{{ cartCount }}</span>
{% endif %}

👉 Améliore l’expérience utilisateur avec un feedback visuel immédiat

📸 Zone screenshot — Navbar + panier

14.6 Gestion des promotions
{% if product.promotion %}
    <p class="old-price">{{ product.price }} €</p>
    <p class="new-price">
        {{ product.price - (product.price * product.promotion.discountPercentage / 100) }} €
    </p>
{% else %}
    <p>{{ product.price }} €</p>
{% endif %}

👉 Fonctionnement :

Vérifie l’existence d’une promotion

Calcule le prix réduit dynamiquement

Met en valeur les offres commerciales

📸 Zone screenshot — Promotion affichée

14.7 Gestion des formulaires

Symfony Form est utilisé pour simplifier la gestion des formulaires.

Exemple : formulaire d’adresse
{{ form_start(form) }}
    {{ form_row(form.street) }}
    {{ form_row(form.city) }}
    {{ form_row(form.postalCode) }}
    <button type="submit">Valider</button>
{{ form_end(form) }}

👉 Avantages :

Sécurité intégrée (CSRF)

Validation automatique

Gain de temps de développement

📸 Zone screenshot — Formulaire checkout

14.8 Interaction utilisateur (UX dynamique)
Messages flash
{% for message in app.flashes('success') %}
    <div class="alert alert-success">{{ message }}</div>
{% endfor %}

👉 Permet :

Informer l’utilisateur

Améliorer la compréhension des actions

Fonctionnalités UX importantes

Mise à jour du panier

Feedback visuel immédiat

Navigation fluide

Réduction des erreurs utilisateur

14.9 Responsive Design

L’interface est entièrement responsive grâce à :

Bootstrap

Grille responsive

Menu mobile (burger menu)

📸 Zone screenshot — Version mobile

14.10 Accessibilité et bonnes pratiques

Utilisation de balises HTML sémantiques

Texte alternatif pour les images

Contraste visuel adapté

Navigation clavier possible

14.11 Conclusion

La partie front-end de l’application Sports Bottles repose sur :

Une architecture claire basée sur Twig

Une gestion dynamique des données

Une expérience utilisateur fluide et intuitive

Elle permet de relier efficacement :

Les données du back-end

Les interactions utilisateur

Les exigences d’un site e-commerce moderne