Annexes
Annexe A — Documents techniques du projet

Le projet Sports Bottles est accompagné d’une documentation technique organisée sous forme de fichiers Markdown permettant de faciliter la compréhension, l’installation et la maintenance de l’application.

Les principaux documents fournis sont :

README.md : présentation générale du projet et instructions d’installation
docker.md : configuration Docker (PHP 8.4, Nginx, MySQL, Mailer, Stripe)
front-end.md : conception UX, intégration Twig et logique d’affichage
architecture.md : architecture MVC Symfony et organisation des services
test.md : stratégie de tests unitaires et validation fonctionnelle

Ces documents constituent un support technique complet pour reproduire l’environnement et comprendre l’organisation du projet.

Annexe B — Extrait de l’entité Product

L’entité Product représente un produit vendable dans l’application. Elle contient les informations nécessaires à l’affichage du catalogue et à la gestion du stock.

#[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
private ?string $price = null;

#[ORM\Column]
private ?int $stock = null;

#[ORM\Column(length: 50)]
private ?string $capacity = null;

#[ORM\Column(length: 50, nullable: true)]
private ?string $temperature = null;

Ces champs permettent notamment de gérer le prix, la disponibilité du produit ainsi que ses caractéristiques techniques.

Annexe C — Extrait administration du stock

Le stock est administrable depuis le back-office via EasyAdmin :

NumberField::new('stock')->setLabel('Stock')

Ce champ permet à l’administrateur de modifier la quantité disponible pour chaque produit.

Annexe D — Données d’initialisation

Des fixtures Doctrine sont utilisées pour initialiser la base de données avec des données cohérentes :

4 catégories de produits
plusieurs produits par catégorie
stock initial de 100 unités par produit
promotions actives sur certains produits

Ces données permettent de tester rapidement l’ensemble du parcours e-commerce.

Annexe E — Commandes utiles

Commandes principales utilisées pour l’installation et la maintenance du projet :

composer install
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
php bin/console cache:clear
php bin/console debug:router

Dans un environnement Docker, ces commandes sont exécutées à l’intérieur du conteneur applicatif Symfony