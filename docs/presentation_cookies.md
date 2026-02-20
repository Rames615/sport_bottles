# Présentation — Consentement aux cookies (modification récente)

## Contexte
Lors de la revue du projet pour le jury, nous avons ajouté un module de consentement aux cookies afin de respecter les bonnes pratiques en matière de protection des données et d'informer l'utilisateur au premier accès.

## Comportement implémenté
- Un modal Bootstrap (`#cookieConsentModal`) s'affiche lors de la première visite.
- L'utilisateur peut **Accepter** ou **Refuser** les cookies.
- Le choix est enregistré dans `localStorage` sous la clé `cookies_accepted` avec les valeurs `accepted` ou `declined`.
- Une fois choisi, le modal ne s'affiche plus pour cet utilisateur sur ce navigateur.

## Pourquoi cette solution
- Simple à intégrer et fiable : `localStorage` persiste la préférence même après fermeture du navigateur.
- Respecte l'expérience utilisateur : modal à choix clair et refus possible.
- Ne bloque pas le rendu du site si JavaScript est désactivé (dégradation élégante) — cependant le consentement nécessite JS pour l'UI.

## Emplacement du code
- Template modal : `templates/base.html.twig` (markup du modal ajouté)
- Logique JS : `assets/cookie-consent.js`
- Import dans les assets : `assets/app.js` (import ajouté)

## Détails techniques
- Le modal utilise une configuration `backdrop: 'static'` pour s'assurer que l'utilisateur prenne un choix explicite.
- Le fichier `cookie-consent.js` vérifie `localStorage` avant d'afficher le modal, et stocke la réponse.
- Pour la production, il est recommandé d'ajouter un lien vers la politique de confidentialité (`Privacy Policy`) dans le modal et d'enregistrer le consentement côté serveur si nécessaire pour des raisons de conformité stricte.

## Tests recommandés
1. Ouvrir le site en navigation privée et vérifier que le modal apparaît.
2. Cliquer sur **Accepter** : vérifier que `localStorage.getItem('cookies_accepted') === 'accepted'` et que le modal ne réapparaît plus.
3. Répéter en cliquant sur **Refuser** et vérifier la valeur `declined`.

## Remarques de sécurité et vie privée
- `localStorage` est suffisant pour la préférence UX, mais si vous devez prouver le consentement (audit), envisagez d'enregistrer la décision côté serveur associée à l'utilisateur connecté.
- Ne placez pas de cookies non essentiels avant consentement explicite (si votre site respecte la réglementation RGPD stricte). Cette implémentation se concentre sur l'UI et le stockage du choix.

---

Pour la soutenance, on peut présenter une démo rapide : ouvrir en navigation privée, accepter, puis montrer que la modal ne revient plus.
