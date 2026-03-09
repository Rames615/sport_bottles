<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\Promotion;
use Doctrine\ORM\EntityManagerInterface;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        $userCount = $this->entityManager->getRepository(User::class)->count([]);
        $productCount = $this->entityManager->getRepository(Product::class)->count([]);
        $categoryCount = $this->entityManager->getRepository(Category::class)->count([]);
        $cartCount = $this->entityManager->getRepository(Cart::class)->count([]);
        $orderCount = $this->entityManager->getRepository(Order::class)->count([]);
        $promotionCount = $this->entityManager->getRepository(Promotion::class)->count([]);

        return $this->render('admin/dashboard.html.twig', compact(
            'userCount',
            'productCount',
            'categoryCount',
            'cartCount',
            'orderCount',
            'promotionCount'
        ));
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Sports Bottles');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-dashboard');
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', 'App\Entity\User');
        yield MenuItem::linkToCrud('Produits', 'fa fa-box', 'App\Entity\Product');
        yield MenuItem::linkToCrud('Categories', 'fa fa-tags', 'App\Entity\Category');
        yield MenuItem::linkToCrud('Commandes', 'fa fa-shopping-cart', Order::class);
        yield MenuItem::linkToCrud('Promotions', 'fa fa-tags', Promotion::class);
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}
