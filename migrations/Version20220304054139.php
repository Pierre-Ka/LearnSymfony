<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220304054139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add createdAt and updatedAt field in Pin table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pin ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NULL, ADD updated_at DATETIME NULL');
        $this->addSql('INSERT INTO pin (created_at) values (NOW())');
        $this->addSql('ALTER TABLE pin CHANGE created_at created_at DATETIME NOT NULL');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pin DROP created_at, DROP updated_at');
    }
}
