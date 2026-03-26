# 14. Partie Front-End : conception de l'interface, intégration Twig et application de Tailwind CSS

Cette partie du dossier projet présente la couche front-end de l'application Sports Bottles. Elle décrit les choix de conception visuelle, les principes UX retenus, l'integration des vues Twig et l'utilisation de Tailwind CSS pour produire une interface responsive, coherente et maintenable.

L'objectif n'est pas seulement de montrer l'apparence du site, mais de demontrer comment l'interface traduit les besoins fonctionnels du projet e-commerce : consultation du catalogue, mise en avant des promotions, ajout au panier, saisie de l'adresse de livraison et accompagnement de l'utilisateur jusqu'au paiement.

---

## 14.1 Objectifs de la couche front-end

La partie front-end a ete concue pour repondre a plusieurs objectifs complementaires :

- proposer une interface claire et immediate a comprendre ;
- valoriser les produits et les promotions ;
- guider l'utilisateur dans son parcours d'achat ;
- fournir des retours visuels rapides apres chaque action ;
- garantir une experience fluide sur mobile, tablette et desktop ;
- conserver une structure de code simple a maintenir dans Symfony.

Dans cette application, le front-end joue donc un role a la fois fonctionnel et commercial. Il ne se limite pas a l'affichage : il participe directement a la conversion en rendant les actions visibles, les informations lisibles et les etapes du tunnel de commande comprehensibles.

---

## 14.2 Organisation de l'interface dans Symfony

L'affichage est genere par Twig. Les templates sont classes par domaine fonctionnel, ce qui facilite la lecture du projet et la maintenance des pages :

- `templates/base.html.twig` pour la structure globale ;
- `templates/nav.html.twig` pour la barre de navigation ;
- `templates/home/index.html.twig` pour la page d'accueil ;
- `templates/product/` pour le catalogue et les cartes produit ;
- `templates/cart/` pour le panier ;
- `templates/checkout/` pour les etapes de commande ;
- `templates/components/` pour les blocs reutilisables, comme les messages flash.

Le layout principal centralise les ressources communes : polices, feuille de style compilee, navigation, footer, scripts front-end et composants globaux.

### Extrait du layout principal

```twig
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{% block title %}Sports Bottles - Bouteilles Durables{% endblock %}</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('styles/app.css') }}">

    {% block stylesheets %}{% endblock %}
    {% block importmap %}{{ importmap('app') }}{% endblock %}
</head>
<body>
    {% include 'nav.html.twig' %}

    <main>
        {% block body %}{% endblock %}
    </main>

    {% include 'footer.html.twig' %}
</body>
</html>
```

Cet extrait montre que la structure de page est mutualisee. Cette approche garantit une coherence visuelle sur l'ensemble du site et evite la duplication de code HTML.

---

## 14.3 Application de Tailwind CSS dans le projet

L'interface repose principalement sur Tailwind CSS. Le projet adopte une logique "utility-first" : les styles sont appliques directement dans les templates Twig au moyen de classes courtes et combinables comme `flex`, `grid`, `rounded-2xl`, `text-gray-900` ou `shadow-sm`.

Ce choix apporte plusieurs avantages :

- rapidite d'integration des maquettes ;
- uniformisation du design ;
- reduction du CSS specifique ;
- meilleure lisibilite des intentions visuelles directement dans le template ;
- adaptation simple aux breakpoints responsives.

### Configuration Tailwind

```js
module.exports = {
  content: [
    './templates/**/*.html.twig',
    './assets/**/*.js',
    './public/scripts/**/*.js',
  ],

  safelist: [
    'cart-badge-visible',
    'badge-pop',
    'hero-toast',
    'cart-toast',
    'no-products-message',
    'active',
    'nav-cart-link',
    'category-tab',
    'category-section',
    'product-card',
    'empty-category',
    'products-grid',
    'product-name',
    'open',
  ],

  theme: {
    extend: {
      colors: {
        primary: '#1F7A63',
        secondary: '#0F172A',
        accent: '#22C55E',
        'text-main': '#0F172A',
        'text-muted': '#64748B',
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
        display: ['Poppins', 'Inter', 'sans-serif'],
      },
    },
  },

  plugins: [],
};
```

Cette configuration montre que Tailwind scanne les templates Twig et les scripts JavaScript afin de ne generer que les classes utiles. Le theme a egalement ete personnalise avec une palette de couleurs et des polices correspondant a l'identite visuelle du projet.

### Point d'entree CSS

```css
@import './variables.css';

@tailwind base;
@tailwind components;
@tailwind utilities;

@layer components {
  .no-products-message {
    @apply col-span-full p-12 text-center bg-blue-50 border border-blue-200 rounded-md text-blue-700;
  }

  .hero-toast {
    @apply rounded-xl;
    animation: slideInRight 0.3s ease-out;
  }

  .open {
    @apply block;
  }
}
```

Ce second extrait montre que Tailwind n'est pas utilise uniquement sous forme de classes inline. Le projet exploite aussi `@layer components` pour definir quelques composants reutilisables et des classes qui doivent rester disponibles meme lorsqu'elles sont ajoutees dynamiquement par JavaScript.

---

## 14.4 Conception UX et hierarchisation visuelle

L'ergonomie de Sports Bottles repose sur plusieurs principes :

- mise en avant immediate de la proposition de valeur ;
- parcours de navigation court vers le catalogue ;
- valorisation des promotions ;
- lisibilite des prix et des appels a l'action ;
- retours visuels apres interaction ;
- reduction de la charge cognitive pendant le checkout.

La page d'accueil illustre bien cette demarche. Le hero affiche un message fort, des boutons d'action visibles et un contraste important entre l'arriere-plan et les zones de texte.

### Extrait a inserer a la place de la capture de la page d'accueil

```twig
<section class="relative min-h-[calc(100vh-70px)] flex items-center justify-center lg:flex-col mt-[70px] overflow-hidden">
    <div class="absolute inset-0 z-[1]">
        <img src="/heroNew.png" alt="Hero image" class="w-full h-full object-cover block">
        <div class="absolute inset-0" style="background: linear-gradient(180deg, rgba(0,0,0,.55) 0%, rgba(0,0,0,.35) 40%, rgba(0,0,0,.65) 100%);"></div>
    </div>

    <div class="relative z-[3] text-center px-5 max-w-[900px] flex flex-col items-center animate-float-hero lg:flex-1 lg:justify-center">
        <h1 class="text-5xl font-extrabold leading-[1.1] tracking-[-1px] text-white">
            <span class="block" style="background: linear-gradient(90deg, #00ff99, #00d4ff); background-clip: text; -webkit-text-fill-color: transparent;">
                Des bouteilles durables
            </span>
            <span class="block mt-2.5 font-semibold opacity-90">pour un mode de vie actif</span>
        </h1>

        <div class="flex justify-center gap-5 flex-wrap lg:absolute lg:bottom-[50px] lg:left-1/2 lg:-translate-x-1/2">
            <a href="{{ path('app_product') }}" class="px-[30px] py-[14px] rounded-full text-[.9rem] font-semibold no-underline shadow-cta">
                DECOUVRIR NOS PRODUITS
            </a>
            <a href="#products" class="px-[30px] py-[14px] rounded-full text-[.9rem] font-semibold no-underline border">
                VOIR PLUS
            </a>
        </div>
    </div>
</section>
```

Ce bloc montre l'utilisation conjointe de Twig et de Tailwind pour produire une zone hero immersive. Les classes Tailwind controlent ici l'alignement, l'espacement, la typographie, la superposition des calques et l'adaptation responsive.

---

## 14.5 Navigation, compte utilisateur et panier

La navigation a ete pensee comme un point d'acces rapide aux fonctionnalites principales : catalogue, contact, panier, profil, commandes et connexion. L'affichage varie selon l'etat d'authentification de l'utilisateur, ce qui permet d'epurer l'interface tout en gardant les actions utiles a portee de clic.

Le panier est egalement mis en avant dans la barre de navigation a travers un badge dynamique. Cette information visuelle immediate permet a l'utilisateur de suivre l'etat de son achat sans changer de page.

### Extrait a inserer a la place de la capture de la navbar et du panier

```twig
{% if app.user %}
{% set cartCount = cart_item_count is defined ? cart_item_count(app.user) : 0 %}

<a class="nav-link nav-cart-link flex items-center gap-[7px] px-3 py-[6px] rounded-[10px] text-white no-underline transition-colors duration-200 hover:bg-primary/20"
   href="{{ path('app_cartindex') }}">
    <span class="nav-cart-icon-wrapper relative inline-flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
            <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1z"/>
        </svg>

        <span id="cart-count-badge"
              class="cart-count-badge{{ cartCount > 0 ? ' cart-badge-visible' : '' }}"
              aria-live="polite">
            {{ cartCount > 0 ? cartCount : '' }}
        </span>
    </span>

    <span class="nav-cart-label hidden md:inline font-medium text-[.9rem]">Panier</span>
</a>
{% endif %}
```

Cet extrait illustre deux mecanismes importants :

- l'affichage conditionnel avec Twig en fonction de l'utilisateur connecte ;
- l'utilisation de classes Tailwind responsives comme `hidden md:inline` pour adapter le rendu selon la taille de l'ecran.

---

## 14.6 Catalogue produit et logique d'affichage des cartes

Le catalogue constitue le coeur fonctionnel de la boutique. Chaque produit est presente dans une carte qui regroupe les informations essentielles a la decision d'achat : image, designation, capacite, categorie, temperature et prix.

Le composant de carte a ete pense pour etre reutilisable dans plusieurs contextes. Il combine lisibilite, mise en valeur commerciale et sobriete visuelle.

### Extrait a inserer a la place de la capture du catalogue produits

```twig
<div class="product-card group flex flex-col h-full bg-white border border-[#dee2e6] rounded-md shadow-[0_2px_4px_rgba(0,0,0,.075)] transition-all duration-300 overflow-hidden hover:shadow-[0_8px_16px_rgba(0,0,0,.15)] hover:-translate-y-1"
     data-product-id="{{ product.id }}"
     data-category="{{ product.category ? product.category.name : 'Uncategorized' }}">

    <div class="relative w-full h-[200px] overflow-hidden bg-[#f8f9fa] shrink-0">
        <img src="{{ productImagePath }}"
             alt="{{ product.designation }}"
             class="w-full h-full object-contain transition-transform duration-300 group-hover:scale-105">
    </div>

    <div class="grow flex flex-col px-5 py-5">
        <h3 class="product-name text-[1.1rem] font-semibold text-[#212529] mb-3 leading-[1.3] line-clamp-2">
            {{ product.designation }}
        </h3>

        <div class="flex flex-col gap-2 mb-4 py-3 border-t border-b border-[#dee2e6] text-sm">
            {% if product.capacity %}
                <div class="flex justify-between flex-wrap gap-2 text-[#555]">
                    <strong class="font-semibold text-[#212529]">Capacite:</strong>
                    <span class="text-[#007bff] font-medium">{{ product.capacity }}</span>
                </div>
            {% endif %}
        </div>

        <p class="text-2xl font-bold text-[#28a745] mt-3 mb-3">
            {{ product.price|number_format(2, ',', ' ') }} EUR
        </p>

        <div class="flex gap-3 mt-auto">
            <a href="{{ path('app_product_show', {id: product.id}) }}"
               class="flex flex-1 items-center justify-center gap-2 px-4 py-2 bg-[#17a2b8] text-white text-sm font-semibold rounded no-underline transition-colors duration-200 hover:bg-[#138496]">
                Voir plus
            </a>
        </div>
    </div>
</div>
```

Cette implementation montre l'apport de Tailwind pour construire des composants metier rapidement. Les classes utilitaires servent ici a gerer la hauteur des cartes, les ombres, l'effet de survol, la typographie, les bordures et la repartition verticale du contenu.

---

## 14.7 Mise en valeur des promotions

Les promotions sont traitees comme un element fort de l'interface. Elles disposent d'une section dediee sur la page d'accueil et d'une presentation plus visuelle que le catalogue standard. L'objectif est de capter l'attention de l'utilisateur en mettant en avant la reduction, la periode de validite et le prix final.

### Extrait a inserer a la place de la capture des promotions

```twig
{% for promotion in promotions %}
    {% set product = promotion.getProduct() %}
    {% if product %}
        <div class="group bg-white rounded-2xl overflow-hidden shadow-[0_2px_12px_rgba(0,0,0,.06)] transition-all duration-[400ms] relative flex flex-col h-full border border-primary/[.08] hover:-translate-y-[9px] hover:shadow-[0_16px_40px_rgba(31,122,99,.12)]">

            <div class="absolute top-0 left-0 right-0 flex items-center gap-1.5 px-2.5 py-[9px] backdrop-blur-[10px] z-10 border-b border-primary/10"
                 style="background: linear-gradient(135deg, rgba(255,255,255,.96) 0%, rgba(255,255,255,.92) 100%);">
                {% if promotion.getDiscountType() == 'percentage' %}
                    <span class="px-2.5 py-1.5 rounded-lg font-bold text-[.75rem] text-white" style="background: linear-gradient(135deg, #dc2626, #bb1c27);">
                        -{{ promotion.getDiscountValue() }}%
                    </span>
                {% endif %}
                <span class="ml-auto px-2 py-1 bg-primary text-white text-[.55rem] font-bold uppercase rounded-md">En promotion</span>
            </div>

            <div class="px-5 py-4 flex-1 flex flex-col">
                <h3 class="text-[1rem] font-bold text-main mb-2 font-display">{{ product.designation }}</h3>

                <div class="mb-3 p-[9px] rounded-lg border border-primary/[.12]"
                     style="background: linear-gradient(135deg, rgba(31,122,99,.06) 0%, rgba(31,122,99,.02) 100%);">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-[.8rem] text-muted font-medium line-through">{{ product.price }}EUR</span>
                        <span class="text-[1.4rem] font-bold text-primary font-display">{{ product.getFinalPrice() | number_format(2, ',', ' ') }}EUR</span>
                    </div>
                </div>
            </div>
        </div>
    {% endif %}
{% endfor %}
```

Sur le plan UX, cette presentation cree une hierarchie visuelle tres claire : badge de reduction, nom du produit, ancien prix barre, nouveau prix et etat promotionnel. La promotion devient ainsi un levier d'attention immediat dans le parcours utilisateur.

---

## 14.8 Formulaires, validation et tunnel de commande

Le tunnel de commande a ete concu pour limiter les erreurs et guider l'utilisateur. La saisie de l'adresse de livraison illustre bien ce travail : les champs sont regroupes, les erreurs sont visibles, les boutons d'action sont explicites et un resume de commande reste accessible sur le cote en version desktop.

### Extrait a inserer a la place de la capture du formulaire de checkout

```twig
<form method="post" class="needs-validation" novalidate>
    {% if form._token is defined %}
        <input type="hidden" name="{{ form._token.vars.full_name }}" value="{{ form._token.vars.value }}">
    {% endif %}

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label for="shipping_address_fullName" class="block text-sm font-semibold text-gray-700 mb-1">
                {{ form.fullName.vars.label }}
                <span class="text-red-500">*</span>
            </label>

            {% if form.fullName.vars.errors|length > 0 %}
                <div class="text-sm text-red-600 mb-2">
                    {% for error in form.fullName.vars.errors %}
                        <div>{{ error.message }}</div>
                    {% endfor %}
                </div>
            {% endif %}

            <input
                type="text"
                id="shipping_address_fullName"
                name="{{ form.fullName.vars.full_name }}"
                class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 {{ form.fullName.vars.errors|length > 0 ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-primary' }}"
                value="{{ form.fullName.vars.value ?? '' }}"
                placeholder="Jean Dupont"
                required
            >
        </div>
    </div>

    <div class="flex flex-col sm:flex-row justify-end gap-3">
        <a href="{{ path('app_cartindex') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2 border border-gray-300 text-gray-700 text-sm font-semibold rounded-md hover:bg-gray-50 transition-colors">
            Retour au panier
        </a>
        <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2 bg-green-600 text-white text-sm font-semibold rounded-md hover:bg-green-700 transition-colors">
            Continuer vers le paiement
        </button>
    </div>
</form>
```

Cet extrait montre plusieurs bonnes pratiques :

- protection CSRF ;
- affichage immediat des erreurs de validation ;
- mise en forme responsive avec `grid` et `md:grid-cols-2` ;
- hierarchisation claire entre action secondaire et action principale.

---

## 14.9 Feedback utilisateur et affichage conditionnel

Une application e-commerce doit informer clairement l'utilisateur des consequences de ses actions. Sports Bottles utilise des messages flash pour afficher des confirmations, alertes ou erreurs. Ce mecanisme ameliore la comprehension du parcours et limite les ambiguities.

### Extrait a inserer a la place de la capture des messages utilisateur

```twig
{% set colorMap = {
    'success': 'bg-green-50 border-green-200 text-green-700',
    'error':   'bg-red-50 border-red-200 text-red-700',
    'danger':  'bg-red-50 border-red-200 text-red-700',
    'warning': 'bg-yellow-50 border-yellow-200 text-yellow-800',
    'info':    'bg-blue-50 border-blue-200 text-blue-700'
} %}

{% for label, messages in app.flashes %}
    {% for message in messages %}
        {% set cls = colorMap[label] ?? colorMap['info'] %}
        <div class="flex items-start gap-3 p-4 mb-3 border rounded-lg {{ cls }}" role="alert">
            <span class="flex-1 text-sm">{{ message }}</span>
            <button type="button" class="ml-auto opacity-60 hover:opacity-100 text-current leading-none" data-bs-dismiss="alert" aria-label="Close">X</button>
        </div>
    {% endfor %}
{% endfor %}
```

Le rendu change selon le type de message grace a une table de correspondance. Cette approche evite la duplication et garantit une presentation homogene pour l'ensemble des retours systeme.

---

## 14.10 Responsive design et adaptation multi-supports

Le responsive design est assure principalement par Tailwind CSS. Le projet exploite ses prefixes de breakpoints pour modifier l'affichage selon la largeur de l'ecran :

- `sm:` pour les petits ecrans ;
- `md:` pour les tablettes ;
- `lg:` pour les ecrans plus larges.

Ce fonctionnement apparait dans plusieurs zones du projet :

- navbar avec bouton mobile visible uniquement sur petits ecrans ;
- formulaires affiches en une ou deux colonnes selon la largeur ;
- cartes produits organisees en grille flexible ;
- resume de commande positionne en colonne laterale sur desktop ;
- certains labels affiches uniquement a partir de `md` pour eviter la surcharge sur mobile.

### Extrait representatif du responsive design

```twig
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        ...
    </div>
</div>

<span class="nav-cart-label hidden md:inline font-medium text-[.9rem]">Panier</span>

<div class="collapse navbar-collapse w-full lg:flex-1" id="navbarNav">
    ...
</div>
```

Ces choix permettent de conserver une interface lisible sans produire plusieurs versions separees des templates.

---

## 14.11 Accessibilite et lisibilite

Plusieurs bonnes pratiques ont ete appliquees dans les templates :

- utilisation d'attributs `alt` sur les images produit ;
- labels explicites dans les formulaires ;
- indication visuelle des champs obligatoires ;
- etats de focus visibles via les classes Tailwind ;
- contraste suffisant sur les boutons et zones d'information ;
- attributs ARIA sur certains composants dynamiques, comme le badge du panier.

Ces points renforcent la qualite d'usage du site et participent a une interface plus inclusive.

---

## 14.13 Animations utilitaires Tailwind dans les templates Stripe

Les pages liées au paiement (`cancel.html.twig`, `complete.html.twig`, `payment_complete.html.twig`) utilisent exclusivement les classes d'animation intégrées à Tailwind CSS, sans aucun `@keyframes` personnalisé ni fichier CSS dédié.

Les classes employées sont les suivantes :

- `animate-bounce` : fait rebondir une icône de confirmation ou d'erreur pour attirer l'attention ;
- `animate-ping` : produit un effet de pulsation concentrique, souvent utilisé comme indicateur d'état actif ;
- `animate-spin` : tourne un élément en continu, typiquement une icône de chargement.

Cette approche garantit que les animations restent cohérentes avec le reste de l'interface, restent optimisées par le JIT Tailwind et ne nécessitent aucun outillage CSS supplémentaire.

---

## 14.14 JavaScript externe et pattern window.PaymentConfig

Le fichier `public/scripts/payment.js` contient la totalité de la logique du formulaire de paiement Stripe : montage de l'élément de carte, validation en temps réel, création du PaymentIntent côté serveur, confirmation de la carte et gestion des erreurs.

Ce script est chargé via une balise `<script src="/scripts/payment.js">` dans `payment.html.twig`. Pour éviter d'inliner des valeurs dynamiques (clé publique Stripe, URLs d'API générées par Symfony) dans un fichier statique, un objet de configuration est exposé juste avant le chargement du script :

```twig
<script>
    window.PaymentConfig = {
        stripePublicKey: "{{ stripe_public_key }}",
        createIntentUrl: "{{ path('app_payment_create_intent') }}",
        confirmUrl:      "{{ path('app_payment_confirm') }}"
    };
</script>
<script src="/scripts/payment.js"></script>
```

`payment.js` consomme ensuite cet objet :

```js
const config = window.PaymentConfig || {};
const stripe  = Stripe(config.stripePublicKey);
```

Cette séparation permet de :

- conserver `payment.js` en tant que fichier statique versionnable ;
- injecter les données dynamiques via Twig sans coupler le script au rendu serveur ;
- éviter tout `eval()` ou interpolation de chaînes non sécurisée.

---

## 14.12 Bilan de la partie front-end

La partie front-end de Sports Bottles s'appuie sur une combinaison coherente de Twig et Tailwind CSS. Cette architecture permet :

- de structurer proprement les vues ;
- de produire une interface moderne et responsive ;
- d'afficher dynamiquement les donnees metier ;
- d'accompagner l'utilisateur dans ses actions ;
- de conserver un code facilement maintenable dans le cadre d'un projet Symfony.

Le choix de Tailwind CSS s'est revele pertinent pour accelerer l'integration, standardiser les composants visuels et garder un lien direct entre intention graphique et implementation technique. Dans le cadre du dossier projet, cette partie montre que le front-end ne constitue pas seulement une couche decorative, mais un veritable support de l'experience utilisateur et de la logique commerciale de l'application.