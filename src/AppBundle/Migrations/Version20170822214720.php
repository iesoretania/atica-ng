<?php

namespace AppBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170822214720 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE documentation_folder (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, organization_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, lft INT NOT NULL, lvl INT NOT NULL, rght INT NOT NULL, document_flow TINYINT(1) NOT NULL, version_shown TINYINT(1) NOT NULL, public TINYINT(1) NOT NULL, public_token VARCHAR(255) DEFAULT NULL, INDEX IDX_B979A09B727ACA70 (parent_id), INDEX IDX_B979A09B32C8A3DE (organization_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE folder_permission (folder_id INT NOT NULL, profile_id INT NOT NULL, permission INT NOT NULL, INDEX IDX_B07B22B162CB942 (folder_id), INDEX IDX_B07B22BCCFA12B8 (profile_id), PRIMARY KEY(folder_id, profile_id, permission)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE documentation_folder ADD CONSTRAINT FK_B979A09B727ACA70 FOREIGN KEY (parent_id) REFERENCES documentation_folder (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE documentation_folder ADD CONSTRAINT FK_B979A09B32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE folder_permission ADD CONSTRAINT FK_B07B22B162CB942 FOREIGN KEY (folder_id) REFERENCES documentation_folder (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE folder_permission ADD CONSTRAINT FK_B07B22BCCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE documentation_folder DROP FOREIGN KEY FK_B979A09B727ACA70');
        $this->addSql('ALTER TABLE folder_permission DROP FOREIGN KEY FK_B07B22B162CB942');
        $this->addSql('DROP TABLE documentation_folder');
        $this->addSql('DROP TABLE folder_permission');
    }
}
