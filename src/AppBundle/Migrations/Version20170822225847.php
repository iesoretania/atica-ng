<?php

namespace AppBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170822225847 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE documentation_entry (id INT AUTO_INCREMENT NOT NULL, folder_id INT NOT NULL, link_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, position INT NOT NULL, retired_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_F5AEE4C7162CB942 (folder_id), INDEX IDX_F5AEE4C7ADA40271 (link_id), INDEX IDX_F5AEE4C7B03A8386 (created_by_id), INDEX IDX_F5AEE4C7896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE documentation_history (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, entry_id INT NOT NULL, comment LONGTEXT NOT NULL, event INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_A5E75F23B03A8386 (created_by_id), INDEX IDX_A5E75F23BA364942 (entry_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE documentation_version (id INT AUTO_INCREMENT NOT NULL, entry_id INT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, state INT NOT NULL, state_changed_at DATETIME NOT NULL, version_nr INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_3D41FCABBA364942 (entry_id), INDEX IDX_3D41FCABB03A8386 (created_by_id), INDEX IDX_3D41FCAB896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE documentation_entry ADD CONSTRAINT FK_F5AEE4C7162CB942 FOREIGN KEY (folder_id) REFERENCES documentation_folder (id)');
        $this->addSql('ALTER TABLE documentation_entry ADD CONSTRAINT FK_F5AEE4C7ADA40271 FOREIGN KEY (link_id) REFERENCES documentation_entry (id)');
        $this->addSql('ALTER TABLE documentation_entry ADD CONSTRAINT FK_F5AEE4C7B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE documentation_entry ADD CONSTRAINT FK_F5AEE4C7896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE documentation_history ADD CONSTRAINT FK_A5E75F23B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE documentation_history ADD CONSTRAINT FK_A5E75F23BA364942 FOREIGN KEY (entry_id) REFERENCES documentation_entry (id)');
        $this->addSql('ALTER TABLE documentation_version ADD CONSTRAINT FK_3D41FCABBA364942 FOREIGN KEY (entry_id) REFERENCES documentation_entry (id)');
        $this->addSql('ALTER TABLE documentation_version ADD CONSTRAINT FK_3D41FCABB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE documentation_version ADD CONSTRAINT FK_3D41FCAB896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE documentation_entry DROP FOREIGN KEY FK_F5AEE4C7ADA40271');
        $this->addSql('ALTER TABLE documentation_history DROP FOREIGN KEY FK_A5E75F23BA364942');
        $this->addSql('ALTER TABLE documentation_version DROP FOREIGN KEY FK_3D41FCABBA364942');
        $this->addSql('DROP TABLE documentation_entry');
        $this->addSql('DROP TABLE documentation_history');
        $this->addSql('DROP TABLE documentation_version');
    }
}
