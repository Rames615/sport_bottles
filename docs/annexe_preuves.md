# Annexe — Preuves par sprint

Ce fichier rassemble les listes de commits identifiés par sprint et les commandes utilisées pour les extraire. Les captures d'écran recommandées (PNG) doivent être stockées dans `docs/annexe_preuves/` et nommées clairement (ex. `Sprint1_commits.png`).

---

## Sprint 1 — Conception & architecture (2026-01-05 → 2026-01-18)
Command used:

```bash
git -C c:/laragon/www/sports_bottles log --oneline --decorate --since=2026-01-05 --until=2026-01-18 --date=short
```

Commits (extrait):

- 50a92ae 2026-01-14 feat(cart,ui,stock): stabilize cart flow, improve UI responsi

Recommended screenshot path: `docs/annexe_preuves/Sprint1_commits.png`

---

## Sprint 2 — Modélisation & persistence (2026-01-19 → 2026-02-01)
Command used:

```bash
git -C c:/laragon/www/sports_bottles log --oneline --decorate --since=2026-01-19 --until=2026-02-01 --date=short
```

Commits (extrait):

- 8c21ea8 2026-01-29 code clean
- 5f1bc20 2026-01-22 feat(admin/dashboard): Dashboard added to make it functionning along with crud.
- 42d9ee7 2026-01-21 feat(roles user admin): an email is selected as a user admin role
- c00531f 2026-01-21 code clean
- 6853eb0 2026-01-21 feat(install of easyadmin): debug controller after installing the admin interface and responsive design for cart index
- 4c31c69 2026-01-21 feat: hero image changed.
- 20b2da5 2026-01-20 feat(style): Harmonise the style to avoid the doubled.
- a32221b 2026-01-20 code clean
- c1aff1e 2026-01-20 feat(pdf): A pdf file is added in the step to complete all the links in footer.
- 82b3723 2026-01-20 feat(products_images): The products images arranged well in to the related folder and the targeted path corrected to display them.

Recommended screenshot path: `docs/annexe_preuves/Sprint2_commits.png`

---

## Sprint 3 — Panier & formulaires (2026-02-02 → 2026-02-15)
Command used:

```bash
git -C c:/laragon/www/sports_bottles log --oneline --decorate --since=2026-02-02 --until=2026-02-15 --date=short
```

Commits (extrait):

- (peu ou pas de commits isolés strictement dans cette fenêtre; vérifier issues/PRs si nécessaire)

Note: les commits liés à cette fonctionnalité semblent répartis autour de fin février / début mars. Pour preuve, consulter :

```bash
git -C c:/laragon/www/sports_bottles log --oneline --decorate --since=2026-02-01 --until=2026-02-28 --date=short
```

Recommended screenshot path: `docs/annexe_preuves/Sprint3_commits.png`

---

## Sprint 4 — Paiement & commandes (2026-02-16 → 2026-03-01)
Command used:

```bash
git -C c:/laragon/www/sports_bottles log --oneline --decorate --since=2026-02-16 --until=2026-03-01 --date=short
```

Commits (extrait):

- 072bee1 2026-02-19 feat(installation stripe): Webhook key and stripe method.
- 9aa2343 2026-02-19 installation of stripe
- (autres commits de nettoyage et admin autour du 19–27 février)

Recommended screenshot path: `docs/annexe_preuves/Sprint4_commits.png`

---

## Sprint 5 — Administration & tests (2026-03-02 → 2026-03-15)
Command used:

```bash
git -C c:/laragon/www/sports_bottles log --oneline --decorate --since=2026-03-02 --until=2026-03-15 --date=short
```

Commits (extrait):

- ea0be5c 2026-03-02 feat(stripe): Payement via stripe implemented successfully
- 3d033ef 2026-03-02 feat(detail): Explanation markdown file created for procedure memo.
- 4fb0031 2026-03-02 feat: A new entity promotion is added to make the articles in promotion
- (plusieurs commits 03/03 → 03/14 concernant dashboard, mailer, tests, styles)

Recommended screenshot path: `docs/annexe_preuves/Sprint5_commits.png`

---

## Sprint 6 — Déploiement & documentation (2026-03-16 → 2026-04-05)
Command used:

```bash
git -C c:/laragon/www/sports_bottles log --oneline --decorate --since=2026-03-16 --until=2026-04-05 --date=short
```

Commits (extrait):

- 82633af 2026-03-18 feat(css): Logo css improvised.
- d237c41 2026-03-25 feat(hero): Hero image modified.
- 0fbce59 2026-03-25 feat: Phpmy admin added to docker-compose
- c5e0346 2026-03-26 feat: removed unused files.
- 435b289 2026-03-26 feat: The server apache is replaced by nginx.
- 3c8603f 2026-03-30 feat(phpUnit): The test unit is done to test the functionality.
- 13c2273 2026-04-04 feat: add Hetzner production deployment config (docker-compose.prod, Caddy SSL, env template)
- 1648c2d 2026-04-05 fix: add assets:install to copy EasyAdmin/bundle CSS to public/bundles/

Recommended screenshot path: `docs/annexe_preuves/Sprint6_commits.png`

---

## Instructions pour captures et annexe finale

- Pour chaque sprint, ouvrez la page GitHub du PR ou de la branche et capturez l'onglet "Commits" montrant 3–8 commits (date, message et hash court).
- Sauvegardez les images sous `docs/annexe_preuves/` et nommez-les `SprintN_commits.png`.
- Mettez à jour ce fichier `docs/annexe_preuves.md` en ajoutant les noms de fichiers d'image et les hashes de commits vérifiés.

---

Fichier généré automatiquement par l'assistant.
