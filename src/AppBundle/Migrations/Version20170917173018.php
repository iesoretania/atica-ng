<?php

namespace AppBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170917173018 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE documentation_entry ADD current_version_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE documentation_entry ADD CONSTRAINT FK_F5AEE4C79407EE77 FOREIGN KEY (current_version_id) REFERENCES documentation_version (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F5AEE4C79407EE77 ON documentation_entry (current_version_id)');
        $this->addSql('ALTER TABLE documentation_history ADD version INT NOT NULL, CHANGE comment comment LONGTEXT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE documentation_entry DROP FOREIGN KEY FK_F5AEE4C79407EE77');
        $this->addSql('DROP INDEX UNIQ_F5AEE4C79407EE77 ON documentation_entry');
        $this->addSql('ALTER TABLE documentation_entry DROP current_version_id');
        $this->addSql('ALTER TABLE documentation_history DROP version, CHANGE comment comment LONGTEXT NOT NULL COLLATE utf8_unicode_ci');
    }
}
