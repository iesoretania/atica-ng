<?php

namespace AppBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170822233505 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE documentation_entry ADD element_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE documentation_entry ADD CONSTRAINT FK_F5AEE4C71F1F2A24 FOREIGN KEY (element_id) REFERENCES element (id)');
        $this->addSql('CREATE INDEX IDX_F5AEE4C71F1F2A24 ON documentation_entry (element_id)');
        $this->addSql('ALTER TABLE folder_permission DROP FOREIGN KEY FK_B07B22BCCFA12B8');
        $this->addSql('DROP INDEX IDX_B07B22BCCFA12B8 ON folder_permission');
        $this->addSql('ALTER TABLE folder_permission DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE folder_permission CHANGE profile_id element_id INT NOT NULL');
        $this->addSql('ALTER TABLE folder_permission ADD CONSTRAINT FK_B07B22B1F1F2A24 FOREIGN KEY (element_id) REFERENCES element (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_B07B22B1F1F2A24 ON folder_permission (element_id)');
        $this->addSql('ALTER TABLE folder_permission ADD PRIMARY KEY (folder_id, element_id, permission)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE documentation_entry DROP FOREIGN KEY FK_F5AEE4C71F1F2A24');
        $this->addSql('DROP INDEX IDX_F5AEE4C71F1F2A24 ON documentation_entry');
        $this->addSql('ALTER TABLE documentation_entry DROP element_id');
        $this->addSql('ALTER TABLE folder_permission DROP FOREIGN KEY FK_B07B22B1F1F2A24');
        $this->addSql('DROP INDEX IDX_B07B22B1F1F2A24 ON folder_permission');
        $this->addSql('ALTER TABLE folder_permission DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE folder_permission CHANGE element_id profile_id INT NOT NULL');
        $this->addSql('ALTER TABLE folder_permission ADD CONSTRAINT FK_B07B22BCCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_B07B22BCCFA12B8 ON folder_permission (profile_id)');
        $this->addSql('ALTER TABLE folder_permission ADD PRIMARY KEY (folder_id, profile_id, permission)');
    }
}
