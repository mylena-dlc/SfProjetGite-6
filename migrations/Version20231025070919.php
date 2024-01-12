<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231025070919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gite CHANGE address address VARCHAR(255) NOT NULL, CHANGE cp cp VARCHAR(10) NOT NULL, CHANGE city city VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE picture CHANGE category_id category_id INT DEFAULT NULL, CHANGE description description VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gite CHANGE address address VARCHAR(50) NOT NULL, CHANGE cp cp VARCHAR(50) NOT NULL, CHANGE city city VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE picture CHANGE category_id category_id INT NOT NULL, CHANGE description description VARCHAR(50) NOT NULL');
    }
}
