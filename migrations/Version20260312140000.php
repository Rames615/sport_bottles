<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add customImagePath column to cart_item for storing promotion images
 */
final class Version20260312140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add customImagePath column to CartItem for storing promotion/custom images';
    }

    public function up(Schema $schema): void
    {
        // Add the customImagePath column if it doesn't exist
        $this->addSql('ALTER TABLE cart_item ADD custom_image_path VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Remove the column if rolling back
        $this->addSql('ALTER TABLE cart_item DROP custom_image_path');
    }
}
