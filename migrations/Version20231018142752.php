<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231018142752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE picture ADD category_id INT NOT NULL, ADD gite_id INT NOT NULL');
        $this->addSql('ALTER TABLE picture ADD CONSTRAINT FK_16DB4F8912469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE picture ADD CONSTRAINT FK_16DB4F89652CAE9B FOREIGN KEY (gite_id) REFERENCES gite (id)');
        $this->addSql('CREATE INDEX IDX_16DB4F8912469DE2 ON picture (category_id)');
        $this->addSql('CREATE INDEX IDX_16DB4F89652CAE9B ON picture (gite_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE picture DROP FOREIGN KEY FK_16DB4F8912469DE2');
        $this->addSql('ALTER TABLE picture DROP FOREIGN KEY FK_16DB4F89652CAE9B');
        $this->addSql('DROP INDEX IDX_16DB4F8912469DE2 ON picture');
        $this->addSql('DROP INDEX IDX_16DB4F89652CAE9B ON picture');
        $this->addSql('ALTER TABLE picture DROP category_id, DROP gite_id');
    }
}
