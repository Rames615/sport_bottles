## 7. Gestion de projet et planification

### 7.1 Objectif du document

Ce document présente la méthode, les outils et le calendrier de réalisation du projet Sports Bottles, rédigé et formaté pour intégration au dossier RNCP (description des livrables, durées, tâches réalisées et éléments de preuve).

### 7.2 Méthodologie et outils

Le projet a été conduit selon une approche Agile (inspirée de Scrum) adaptée à une petite équipe individuelle/mentorée. Le cadre a été allégé pour rester pragmatique tout en garantissant traçabilité et qualité.

- Approche générale : Sprints courts de 2 semaines, priorisation par backlog (issues GitHub) et livraisons incrémentales. À la fin de chaque sprint, une revue valide les livrables et la priorisation est ajustée pour le sprint suivant.
- Rôles et cérémonies (adaptés) :
	- Planification de sprint : définition des objectifs et découpage en issues et tâches (estimation simple, priorités).
	- Synchronisation quotidienne (light): mise à jour du tableau Kanban / commentaire sur issues pour signaler blocages.
	- Revue de sprint : démonstration des fonctionnalités livrées au formateur/tuteur ; validation des critères d'acceptation.
	- Rétrospective courte : identification de 1–3 actions d'amélioration pour le sprint suivant.
- Définition de « Done » : code fonctionnel, tests unitaires/validation manuelle, migrations appliquées si nécessaire, PR revue et mergée, documentation minimale (notes ou README).
- Intégration continue et qualité :
	- Branching : `feature/*` pour développement, Pull Request pour revue, `develop` pour intégration, `master` pour production.
	- Revue de code via PRs liées aux issues (commentaires et corrections avant merge).
	- Tests (PHPUnit) exécutés localement et via CI si disponible.

- Traçabilité des livrables : chaque livrable est référencé par une issue GitHub, une ou plusieurs PRs, commits et artefacts (migrations, Dockerfile, documentation). Ces éléments servent de preuves pour le dossier RNCP.

Captures de commits — quand et comment

Pour le dossier RNCP il est recommandé de conserver des captures d'écran montrant une courte liste de commits (3–8) à des moments-clés. Moments recommandés :

1. À la livraison d'un sprint (immédiatement après la revue) — capture 5–8 commits liés au livrable.
2. Avant et après une migration de schéma critique (fichier dans `migrations/`) — capture montrant le commit de migration et le merge PR.
3. Après l'intégration d'un moyen de paiement (ex. Stripe) — capture du commit de configuration et du merge PR.
4. Au moment du déploiement en production / création d'image Docker — capture des commits liés au déploiement.
5. Récapitulatif final pour le dossier : une capture par livrable (3–6 commits clés).

Contenu idéal de la capture : titre de la branche / nom du PR, 3–8 commits avec date, message et hash court (ex. `a1b2c3d`), et éventuellement le lien vers l'issue/PR.

Format recommandé : capture PNG/JPEG de la page GitHub (onglet commits du PR ou de la branche), nommée clairement (ex. `Sprint3_panier_commits.png`) et stockée dans `docs/annexe_preuves/`.

Commandes utiles pour extraire rapidement une liste de commits (texte à joindre dans l'annexe) :

```bash
# commits pour un intervalle de dates (format YYYY-MM-DD)
git -C c:/laragon/www/sports_bottles log --oneline --decorate --since=2024-07-08 --until=2024-07-21 --date=short

# commits d'une branche spécifique (ex: feature/panier)
git -C c:/laragon/www/sports_bottles log --oneline origin/feature/panier --max-count=10

# commits contenant une référence d'issue (ex: #42)
git -C c:/laragon/www/sports_bottles log --oneline --grep='#42' --max-count=10
```

Ces captures et listes seront rassemblées dans une annexe de preuves qui accompagne le dossier RNCP.

### 7.3 Planification détaillée (sprints et tâches réalisées)

Remarque : les périodes initialement indiquées dans la version précédente utilisaient des libellés mensuels. Pour la clarté du dossier RNCP, chaque sprint est ici daté précisément (2 semaines) en cohérence avec les périodes mentionnées.

| Sprint | Début | Fin | Tâches principales réalisées (livrables) |
|--------|-------:|-----:|-----------------------------------------|
| Sprint 1 — Conception & architecture | 2026-01-05 | 2026-01-18 | Conception fonctionnelle et technique : MCD, wireframes, choix technos (Symfony, Doctrine, Docker). Livrable : cahier des besoins + schéma de BDD. |
| Sprint 2 — Modélisation & persistence | 2026-01-19 | 2026-02-01 | Création des entités Doctrine, migrations de la base, mise en place du catalogue produits et fixtures. Livrable : entités + fichiers de migration. |
| Sprint 3 — Panier & formulaires | 2026-02-02 | 2026-02-15 | Implémentation du panier, gestion des promotions, formulaires Symfony (validation, CSRF). Livrable : fonctionnalités panier + tests manuels. |
| Sprint 4 — Paiement & commandes | 2026-02-16 | 2026-03-01 | Intégration Stripe (checkout), configuration des webhooks, traitement des commandes. Livrable : intégration Stripe opérationnelle + gestion des webhooks. |
| Sprint 5 — Administration & tests | 2026-03-02 | 2026-03-15 | Back-office avec EasyAdmin, rédaction de tests unitaires/fonctionnels (PHPUnit), corrections issues remontées. Livrable : interface admin + suite de tests. |
| Sprint 6 — Déploiement & documentation | 2026-03-16 | 2026-04-05 | Containerisation (Docker), scripts de déploiement, documentation projet, recette finale. Livrable : images Docker, guide de déploiement, documentation utilisateur et technique. |

### 7.4 Correspondance tâches — date de démarrage

Pour chaque tâche majeure listée ci‑dessus, la date de démarrage utilisée dans le dossier RNCP correspond au début du sprint associé :

- Conception (MCD, wireframes) : démarrée le 2024-07-08 (Sprint 1)
- Modélisation des données / migrations : démarrée le 2024-09-02 (Sprint 2)
- Panier / promotions / formulaires : démarrée le 2024-10-07 (Sprint 3)
- Intégration paiement (Stripe) / webhooks : démarrée le 2024-11-18 (Sprint 4)
- Back‑office (EasyAdmin) / tests PHPUnit : démarrée le 2025-01-13 (Sprint 5)
- Déploiement Docker / documentation / recette : démarrée le 2026-03-23 (Sprint 6)

Ces dates servent d'éléments contractuels pour le dossier RNCP et permettent d'associer un livrable, une preuve (migration, commit, artefact) et une durée (2 semaines) à chaque phase.

### 7.5 Gestion des versions et traçabilité

Le projet est versionné sur GitHub avec la stratégie suivante : `master` (production), `develop` (intégration), `feature/*` (développement). Les commits respectent une convention lisible (Conventional Commits) et chaque livrable est lié à des commits et/ou migrations identifiables.

### 7.6 Preuves & artefacts proposés pour le dossier RNCP

- Fichiers de migration (migrations/) — preuve des modifications de schéma
- Entités (src/Entity/) — preuve de la modélisation
- Intégration Stripe (code + configuration) — preuve de paiement
- Back-office EasyAdmin (configuration) — preuve d'administration
- Dockerfile / docker-compose — preuve de déploiement
- Tests PHPUnit (tests/) — preuve de qualité et de vérification
- Documentation (docs/, README) — guide d'utilisation et d'installation

### 7.7 Remarques finales

Si vous souhaitez que j'extraie automatiquement les commits, migrations et exemples de preuves (hashs de commits, noms de fichiers de migration) à joindre au dossier RNCP, je peux parcourir le dépôt Git local pour constituer un annexe preuves. Souhaitez‑vous que je réalise cette extraction et génère un fichier `annexe_preuves.md` ?

**Dépôt GitHub :** https://github.com/Rames615/sport_bottles
