# Consignes documentaires

## Objectif

Ce document définit les règles de maintien du corpus `docs/` afin d'éviter le retour des répétitions et des notes de travail trop verbeuses.

## Principes à respecter

### 1. Un document = un sujet principal

Chaque fichier doit traiter un sujet précis : architecture, paiement, produit, tests, profil, mot de passe, etc. Si plusieurs fichiers décrivent exactement la même chose, un seul doit rester détaillé et les autres doivent renvoyer vers lui.

### 2. Séparer l'état courant et l'historique

- les documents de référence décrivent l'état actuel du projet ;
- les documents de correction ou de debug décrivent un problème résolu et doivent rester courts ;
- les notes de présentation doivent rester orientées démonstration ou UX.

### 3. Employer un ton homogène

Le ton attendu est :

- professionnel ;
- explicatif ;
- centré sur les faits ;
- rédigé en français.

### 4. Limiter les répétitions techniques

Les mêmes routes, fichiers ou flux complets ne doivent pas être recopiés dans plusieurs documents sans nécessité. À la place :

- résumer ;
- renvoyer vers le document de référence ;
- préciser uniquement ce qui change pour le sujet traité.

### 5. Priorité de lecture

L'ordre recommandé pour découvrir le projet reste :

1. `docs/readme.md`
2. `docs/GETTING_STARTED.md`
3. `docs/ARCHITECTURE.md`
4. documents spécialisés selon le besoin.

## Format conseillé

Pour chaque nouveau document, privilégier cette structure :

1. objectif ;
2. périmètre ;
3. fonctionnement actuel ;
4. points d'attention ;
5. liens vers les documents liés.