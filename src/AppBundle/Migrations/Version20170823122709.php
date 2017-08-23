<?php

namespace AppBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170823122709 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE historic_period (id INT AUTO_INCREMENT NOT NULL, organization_id INT NOT NULL, name VARCHAR(255) NOT NULL, position INT NOT NULL, INDEX IDX_664ED1E432C8A3DE (organization_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE historic_period ADD CONSTRAINT FK_664ED1E432C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE documentation_entry ADD archived_period_id INT DEFAULT NULL, DROP archived');
        $this->addSql('ALTER TABLE documentation_entry ADD CONSTRAINT FK_F5AEE4C768CFC17E FOREIGN KEY (archived_period_id) REFERENCES historic_period (id)');
        $this->addSql('CREATE INDEX IDX_F5AEE4C768CFC17E ON documentation_entry (archived_period_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE documentation_entry DROP FOREIGN KEY FK_F5AEE4C768CFC17E');
        $this->addSql('DROP TABLE historic_period');
        $this->addSql('DROP INDEX IDX_F5AEE4C768CFC17E ON documentation_entry');
        $this->addSql('ALTER TABLE documentation_entry ADD archived TINYINT(1) NOT NULL, DROP archived_period_id');
    }
}
