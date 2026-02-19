🎯 Conception de la Hero Section
1. Objectif de la Hero Section

La Hero Section constitue la première zone visible de la page d’accueil.
Son rôle est stratégique :

Présenter immédiatement la proposition de valeur

Mettre en avant l’identité de la marque

Créer un impact visuel fort

Inciter à l’action (Call To Action)

Dans notre projet Sports Bottles, l’objectif est de transmettre :

Durabilité • Performance • Modernité • Écoresponsabilité

2. Structure Générale

La Hero Section est composée de quatre éléments principaux :

2.1 Conteneur principal
<section class="hero-section-full">


Ce conteneur :

Occupe toute la hauteur de l’écran (100vh)

Permet un centrage vertical du contenu

Sert de base pour le positionnement absolu du background

2.2 Image de fond
<div class="hero-image-container">
    <img class="hero-main-image">
</div>


Rôle :

Apporter un impact visuel immédiat

Mettre en contexte le produit

Créer une immersion utilisateur

La propriété object-fit: cover permet d’éviter toute déformation de l’image.

2.3 Overlay (Superposition)
.hero-overlay {
    background: linear-gradient(...);
}


Objectif :

Améliorer la lisibilité du texte

Créer une ambiance premium

Ajouter de la profondeur visuelle

L’utilisation d’un dégradé sombre progressif donne un rendu plus professionnel qu’un simple fond opaque.

2.4 Contenu central
<div class="hero-content-wrapper">


Il contient :

Le titre principal (H1)

Le sous-titre

Les boutons d’action

Le contenu est centré horizontalement et verticalement pour un équilibre visuel optimal.

3. Hiérarchie Typographique
3.1 Titre principal (H1)
<h1 class="hero-title-main">


Caractéristiques :

Taille responsive avec clamp()

Poids fort (font-weight: 800)

Espacement négatif pour un style moderne

Le titre est divisé en deux parties :

<span class="hero-highlight">Des bouteilles durables</span>
<span class="hero-rest">pour un mode de vie actif</span>


Cela permet :

De mettre en valeur la proposition principale

D’appliquer un dégradé moderne sur la première ligne

De créer une meilleure hiérarchie visuelle

3.2 Sous-titre
<p class="hero-subtitle">


Fonction :

Apporter des éléments de réassurance

Synthétiser les avantages clés

Rester visuellement léger pour ne pas concurrencer le H1

Le choix d’une opacité réduite renforce l’effet premium.

4. Call To Action (CTA)

Deux boutons sont présents :

Bouton principal (action prioritaire)

Bouton secondaire (navigation complémentaire)

4.1 Bouton principal
.btn-cta-primary


Caractéristiques :

Dégradé dynamique

Ombre douce

Animation légère au survol

Il capte naturellement l’attention.

4.2 Bouton secondaire
.btn-cta-secondary


Caractéristiques :

Style “glass effect”

Bordure subtile

Transparence légère

Il reste visible sans détourner l’attention du bouton principal.

5. Responsive Design

La Hero Section est entièrement responsive :

Desktop (≥ 992px)

Titre centré verticalement

Boutons positionnés en bas de la section

Effet visuel plus aérien

Mobile (≤ 576px)

Taille du titre réduite

Boutons plus compacts

Espacement optimisé

Cela garantit :

Lisibilité

Accessibilité

Cohérence visuelle

6. Animation Subtile

Une animation légère de type floating effect est appliquée :

@keyframes floatHero


Objectif :

Donner de la vie à la section

Éviter un rendu statique

Rester discret et professionnel

Cette animation reste volontairement minimaliste pour ne pas nuire à la crédibilité du site.

7. Choix UX & UI

Les décisions de design reposent sur :

Minimalisme moderne

Hiérarchie claire

Contraste optimisé

Micro-interactions maîtrisées

L’ensemble vise à créer une expérience :

Professionnelle • Fluide • Impactante

8. Résultat Final

La Hero Section :

Valorise immédiatement le produit

Transmet les valeurs écologiques

Guide l’utilisateur vers l’action

Respecte les standards actuels du web moderne (2025)

Elle constitue ainsi un élément central de la stratégie d’engagement du site.