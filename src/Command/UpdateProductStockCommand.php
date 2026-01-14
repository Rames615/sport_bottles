<?php

namespace App\Command;

use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-product-stock',
    description: 'Update product stock to 100 for all products that have stock = 0 or null',
)]
class UpdateProductStockCommand extends Command
{
    public function __construct(
        private ProductRepository $productRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $products = $this->productRepository->findAll();
        $updated = 0;

        foreach ($products as $product) {
            $stock = $product->getStock();
            if ($stock === null || $stock <= 0) {
                $product->setStock(100);
                $this->entityManager->persist($product);
                $updated++;
            }
        }

        if ($updated > 0) {
            $this->entityManager->flush();
            $io->success(sprintf('Updated stock for %d products to 100.', $updated));
        } else {
            $io->info('No products needed stock updates.');
        }

        return Command::SUCCESS;
    }
}
