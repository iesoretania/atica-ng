<?php

namespace AppBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170831011355 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE actor (source_id INT NOT NULL, profile_id INT NOT NULL, INDEX IDX_447556F9953C1C61 (source_id), INDEX IDX_447556F9CCFA12B8 (profile_id), PRIMARY KEY(source_id, profile_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE documentation_entry (id INT AUTO_INCREMENT NOT NULL, folder_id INT NOT NULL, archived_period_id INT DEFAULT NULL, link_id INT DEFAULT NULL, element_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, position INT NOT NULL, retired_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_F5AEE4C7162CB942 (folder_id), INDEX IDX_F5AEE4C768CFC17E (archived_period_id), INDEX IDX_F5AEE4C7ADA40271 (link_id), INDEX IDX_F5AEE4C71F1F2A24 (element_id), INDEX IDX_F5AEE4C7B03A8386 (created_by_id), INDEX IDX_F5AEE4C7896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE documentation_folder (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, organization_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, lft INT NOT NULL, lvl INT NOT NULL, rght INT NOT NULL, document_flow TINYINT(1) NOT NULL, version_shown TINYINT(1) NOT NULL, public TINYINT(1) NOT NULL, public_token VARCHAR(255) DEFAULT NULL, visibility INT NOT NULL, group_by INT NOT NULL, INDEX IDX_B979A09B727ACA70 (parent_id), INDEX IDX_B979A09B32C8A3DE (organization_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE documentation_folder_permission (folder_id INT NOT NULL, element_id INT NOT NULL, permission INT NOT NULL, INDEX IDX_45D5A611162CB942 (folder_id), INDEX IDX_45D5A6111F1F2A24 (element_id), PRIMARY KEY(folder_id, element_id, permission)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE documentation_history (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, entry_id INT NOT NULL, comment LONGTEXT NOT NULL, event INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_A5E75F23B03A8386 (created_by_id), INDEX IDX_A5E75F23BA364942 (entry_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE documentation_task (id INT AUTO_INCREMENT NOT NULL, folder_id INT NOT NULL, periodicity_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, to_date DATETIME DEFAULT NULL, from_date DATETIME DEFAULT NULL, grace_period INT NOT NULL, document_name_template VARCHAR(255) DEFAULT NULL, delivery_type INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_6699A9C8162CB942 (folder_id), INDEX IDX_6699A9C833E79D0D (periodicity_id), INDEX IDX_6699A9C8B03A8386 (created_by_id), INDEX IDX_6699A9C8896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE documentation_task_label (task_id INT NOT NULL, element_id INT NOT NULL, INDEX IDX_8801C4F68DB60186 (task_id), INDEX IDX_8801C4F61F1F2A24 (element_id), PRIMARY KEY(task_id, element_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE documentation_task_entries (task_id INT NOT NULL, entry_id INT NOT NULL, INDEX IDX_EDB5DE18DB60186 (task_id), INDEX IDX_EDB5DE1BA364942 (entry_id), PRIMARY KEY(task_id, entry_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE documentation_task_permission (task_id INT NOT NULL, element_id INT NOT NULL, permission INT NOT NULL, INDEX IDX_BD97D3A58DB60186 (task_id), INDEX IDX_BD97D3A51F1F2A24 (element_id), PRIMARY KEY(task_id, element_id, permission)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE documentation_version (id INT AUTO_INCREMENT NOT NULL, entry_id INT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, state INT NOT NULL, state_changed_at DATETIME NOT NULL, version_nr INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_3D41FCABBA364942 (entry_id), INDEX IDX_3D41FCABB03A8386 (created_by_id), INDEX IDX_3D41FCAB896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE element (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, organization_id INT NOT NULL, managed_by_id INT DEFAULT NULL, profile_id INT DEFAULT NULL, linked_to_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) DEFAULT NULL, folder TINYINT(1) NOT NULL, included TINYINT(1) NOT NULL, locked TINYINT(1) NOT NULL, lft INT NOT NULL, lvl INT NOT NULL, rght INT NOT NULL, description LONGTEXT DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_41405E39727ACA70 (parent_id), INDEX IDX_41405E3932C8A3DE (organization_id), INDEX IDX_41405E39873649CA (managed_by_id), UNIQUE INDEX UNIQ_41405E39CCFA12B8 (profile_id), UNIQUE INDEX UNIQ_41405E398031A592 (linked_to_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE label (element_source INT NOT NULL, element_target INT NOT NULL, INDEX IDX_EA750E8D69D76E7 (element_source), INDEX IDX_EA750E8CF782668 (element_target), PRIMARY KEY(element_source, element_target)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE historic_period (id INT AUTO_INCREMENT NOT NULL, organization_id INT NOT NULL, name VARCHAR(255) NOT NULL, position INT NOT NULL, INDEX IDX_664ED1E432C8A3DE (organization_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE membership (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, organization_id INT NOT NULL, valid_from DATETIME NOT NULL, valid_until DATETIME DEFAULT NULL, INDEX IDX_86FFD285A76ED395 (user_id), INDEX IDX_86FFD28532C8A3DE (organization_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE organization (id INT AUTO_INCREMENT NOT NULL, element_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(255) NOT NULL, zip_code VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(255) DEFAULT NULL, fax_number VARCHAR(255) DEFAULT NULL, email_address VARCHAR(255) DEFAULT NULL, web_site VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_C1EE637C77153098 (code), UNIQUE INDEX UNIQ_C1EE637C1F1F2A24 (element_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE manager (organization_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_FA2425B932C8A3DE (organization_id), INDEX IDX_FA2425B9A76ED395 (user_id), PRIMARY KEY(organization_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE periodicity (id INT AUTO_INCREMENT NOT NULL, organization_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, position INT NOT NULL, day VARCHAR(255) NOT NULL, month VARCHAR(255) NOT NULL, year VARCHAR(255) NOT NULL, day_of_week VARCHAR(255) NOT NULL, INDEX IDX_C53CC5BC32C8A3DE (organization_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile (id INT AUTO_INCREMENT NOT NULL, organization_id INT NOT NULL, code VARCHAR(255) DEFAULT NULL, name_neutral VARCHAR(255) NOT NULL, name_male VARCHAR(255) NOT NULL, name_female VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, initials VARCHAR(255) NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_8157AA0F32C8A3DE (organization_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reference (source_id INT NOT NULL, target_id INT NOT NULL, multiple TINYINT(1) NOT NULL, mandatory TINYINT(1) NOT NULL, INDEX IDX_AEA34913953C1C61 (source_id), INDEX IDX_AEA34913158E0B66 (target_id), PRIMARY KEY(source_id, target_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (element_id INT NOT NULL, user_id INT NOT NULL, profile_id INT NOT NULL, INDEX IDX_57698A6A1F1F2A24 (element_id), INDEX IDX_57698A6AA76ED395 (user_id), INDEX IDX_57698A6ACCFA12B8 (profile_id), PRIMARY KEY(element_id, user_id, profile_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, default_organization_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, login_username VARCHAR(255) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, password_changed_at DATETIME DEFAULT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, global_administrator TINYINT(1) NOT NULL, email_address VARCHAR(255) DEFAULT NULL, internal_code VARCHAR(255) DEFAULT NULL, gender INT NOT NULL, token VARCHAR(255) DEFAULT NULL, token_type VARCHAR(255) DEFAULT NULL, token_expiration DATETIME DEFAULT NULL, last_access DATETIME DEFAULT NULL, blocked_until DATETIME DEFAULT NULL, external_check TINYINT(1) NOT NULL, allow_external_check TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649D6FA26E8 (login_username), UNIQUE INDEX UNIQ_8D93D64935C246D5 (password), UNIQUE INDEX UNIQ_8D93D649B08E074E (email_address), INDEX IDX_8D93D649AA9E0B02 (default_organization_id), INDEX IDX_8D93D649B03A8386 (created_by_id), INDEX IDX_8D93D649896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE actor ADD CONSTRAINT FK_447556F9953C1C61 FOREIGN KEY (source_id) REFERENCES element (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE actor ADD CONSTRAINT FK_447556F9CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE documentation_entry ADD CONSTRAINT FK_F5AEE4C7162CB942 FOREIGN KEY (folder_id) REFERENCES documentation_folder (id)');
        $this->addSql('ALTER TABLE documentation_entry ADD CONSTRAINT FK_F5AEE4C768CFC17E FOREIGN KEY (archived_period_id) REFERENCES historic_period (id)');
        $this->addSql('ALTER TABLE documentation_entry ADD CONSTRAINT FK_F5AEE4C7ADA40271 FOREIGN KEY (link_id) REFERENCES documentation_entry (id)');
        $this->addSql('ALTER TABLE documentation_entry ADD CONSTRAINT FK_F5AEE4C71F1F2A24 FOREIGN KEY (element_id) REFERENCES element (id)');
        $this->addSql('ALTER TABLE documentation_entry ADD CONSTRAINT FK_F5AEE4C7B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE documentation_entry ADD CONSTRAINT FK_F5AEE4C7896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE documentation_folder ADD CONSTRAINT FK_B979A09B727ACA70 FOREIGN KEY (parent_id) REFERENCES documentation_folder (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE documentation_folder ADD CONSTRAINT FK_B979A09B32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE documentation_folder_permission ADD CONSTRAINT FK_45D5A611162CB942 FOREIGN KEY (folder_id) REFERENCES documentation_folder (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE documentation_folder_permission ADD CONSTRAINT FK_45D5A6111F1F2A24 FOREIGN KEY (element_id) REFERENCES element (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE documentation_history ADD CONSTRAINT FK_A5E75F23B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE documentation_history ADD CONSTRAINT FK_A5E75F23BA364942 FOREIGN KEY (entry_id) REFERENCES documentation_entry (id)');
        $this->addSql('ALTER TABLE documentation_task ADD CONSTRAINT FK_6699A9C8162CB942 FOREIGN KEY (folder_id) REFERENCES documentation_folder (id)');
        $this->addSql('ALTER TABLE documentation_task ADD CONSTRAINT FK_6699A9C833E79D0D FOREIGN KEY (periodicity_id) REFERENCES periodicity (id)');
        $this->addSql('ALTER TABLE documentation_task ADD CONSTRAINT FK_6699A9C8B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE documentation_task ADD CONSTRAINT FK_6699A9C8896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE documentation_task_label ADD CONSTRAINT FK_8801C4F68DB60186 FOREIGN KEY (task_id) REFERENCES documentation_task (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE documentation_task_label ADD CONSTRAINT FK_8801C4F61F1F2A24 FOREIGN KEY (element_id) REFERENCES element (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE documentation_task_entries ADD CONSTRAINT FK_EDB5DE18DB60186 FOREIGN KEY (task_id) REFERENCES documentation_task (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE documentation_task_entries ADD CONSTRAINT FK_EDB5DE1BA364942 FOREIGN KEY (entry_id) REFERENCES documentation_entry (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE documentation_task_permission ADD CONSTRAINT FK_BD97D3A58DB60186 FOREIGN KEY (task_id) REFERENCES documentation_task (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE documentation_task_permission ADD CONSTRAINT FK_BD97D3A51F1F2A24 FOREIGN KEY (element_id) REFERENCES element (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE documentation_version ADD CONSTRAINT FK_3D41FCABBA364942 FOREIGN KEY (entry_id) REFERENCES documentation_entry (id)');
        $this->addSql('ALTER TABLE documentation_version ADD CONSTRAINT FK_3D41FCABB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE documentation_version ADD CONSTRAINT FK_3D41FCAB896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE element ADD CONSTRAINT FK_41405E39727ACA70 FOREIGN KEY (parent_id) REFERENCES element (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE element ADD CONSTRAINT FK_41405E3932C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE element ADD CONSTRAINT FK_41405E39873649CA FOREIGN KEY (managed_by_id) REFERENCES element (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE element ADD CONSTRAINT FK_41405E39CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE element ADD CONSTRAINT FK_41405E398031A592 FOREIGN KEY (linked_to_id) REFERENCES element (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE label ADD CONSTRAINT FK_EA750E8D69D76E7 FOREIGN KEY (element_source) REFERENCES element (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE label ADD CONSTRAINT FK_EA750E8CF782668 FOREIGN KEY (element_target) REFERENCES element (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE historic_period ADD CONSTRAINT FK_664ED1E432C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD285A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD28532C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('ALTER TABLE organization ADD CONSTRAINT FK_C1EE637C1F1F2A24 FOREIGN KEY (element_id) REFERENCES element (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE manager ADD CONSTRAINT FK_FA2425B932C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE manager ADD CONSTRAINT FK_FA2425B9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE periodicity ADD CONSTRAINT FK_C53CC5BC32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0F32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('ALTER TABLE reference ADD CONSTRAINT FK_AEA34913953C1C61 FOREIGN KEY (source_id) REFERENCES element (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reference ADD CONSTRAINT FK_AEA34913158E0B66 FOREIGN KEY (target_id) REFERENCES element (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role ADD CONSTRAINT FK_57698A6A1F1F2A24 FOREIGN KEY (element_id) REFERENCES element (id)');
        $this->addSql('ALTER TABLE role ADD CONSTRAINT FK_57698A6AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE role ADD CONSTRAINT FK_57698A6ACCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649AA9E0B02 FOREIGN KEY (default_organization_id) REFERENCES organization (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
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
        $this->addSql('ALTER TABLE documentation_task_entries DROP FOREIGN KEY FK_EDB5DE1BA364942');
        $this->addSql('ALTER TABLE documentation_version DROP FOREIGN KEY FK_3D41FCABBA364942');
        $this->addSql('ALTER TABLE documentation_entry DROP FOREIGN KEY FK_F5AEE4C7162CB942');
        $this->addSql('ALTER TABLE documentation_folder DROP FOREIGN KEY FK_B979A09B727ACA70');
        $this->addSql('ALTER TABLE documentation_folder_permission DROP FOREIGN KEY FK_45D5A611162CB942');
        $this->addSql('ALTER TABLE documentation_task DROP FOREIGN KEY FK_6699A9C8162CB942');
        $this->addSql('ALTER TABLE documentation_task_label DROP FOREIGN KEY FK_8801C4F68DB60186');
        $this->addSql('ALTER TABLE documentation_task_entries DROP FOREIGN KEY FK_EDB5DE18DB60186');
        $this->addSql('ALTER TABLE documentation_task_permission DROP FOREIGN KEY FK_BD97D3A58DB60186');
        $this->addSql('ALTER TABLE actor DROP FOREIGN KEY FK_447556F9953C1C61');
        $this->addSql('ALTER TABLE documentation_entry DROP FOREIGN KEY FK_F5AEE4C71F1F2A24');
        $this->addSql('ALTER TABLE documentation_folder_permission DROP FOREIGN KEY FK_45D5A6111F1F2A24');
        $this->addSql('ALTER TABLE documentation_task_label DROP FOREIGN KEY FK_8801C4F61F1F2A24');
        $this->addSql('ALTER TABLE documentation_task_permission DROP FOREIGN KEY FK_BD97D3A51F1F2A24');
        $this->addSql('ALTER TABLE element DROP FOREIGN KEY FK_41405E39727ACA70');
        $this->addSql('ALTER TABLE element DROP FOREIGN KEY FK_41405E39873649CA');
        $this->addSql('ALTER TABLE element DROP FOREIGN KEY FK_41405E398031A592');
        $this->addSql('ALTER TABLE label DROP FOREIGN KEY FK_EA750E8D69D76E7');
        $this->addSql('ALTER TABLE label DROP FOREIGN KEY FK_EA750E8CF782668');
        $this->addSql('ALTER TABLE organization DROP FOREIGN KEY FK_C1EE637C1F1F2A24');
        $this->addSql('ALTER TABLE reference DROP FOREIGN KEY FK_AEA34913953C1C61');
        $this->addSql('ALTER TABLE reference DROP FOREIGN KEY FK_AEA34913158E0B66');
        $this->addSql('ALTER TABLE role DROP FOREIGN KEY FK_57698A6A1F1F2A24');
        $this->addSql('ALTER TABLE documentation_entry DROP FOREIGN KEY FK_F5AEE4C768CFC17E');
        $this->addSql('ALTER TABLE documentation_folder DROP FOREIGN KEY FK_B979A09B32C8A3DE');
        $this->addSql('ALTER TABLE element DROP FOREIGN KEY FK_41405E3932C8A3DE');
        $this->addSql('ALTER TABLE historic_period DROP FOREIGN KEY FK_664ED1E432C8A3DE');
        $this->addSql('ALTER TABLE membership DROP FOREIGN KEY FK_86FFD28532C8A3DE');
        $this->addSql('ALTER TABLE manager DROP FOREIGN KEY FK_FA2425B932C8A3DE');
        $this->addSql('ALTER TABLE periodicity DROP FOREIGN KEY FK_C53CC5BC32C8A3DE');
        $this->addSql('ALTER TABLE profile DROP FOREIGN KEY FK_8157AA0F32C8A3DE');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649AA9E0B02');
        $this->addSql('ALTER TABLE documentation_task DROP FOREIGN KEY FK_6699A9C833E79D0D');
        $this->addSql('ALTER TABLE actor DROP FOREIGN KEY FK_447556F9CCFA12B8');
        $this->addSql('ALTER TABLE element DROP FOREIGN KEY FK_41405E39CCFA12B8');
        $this->addSql('ALTER TABLE role DROP FOREIGN KEY FK_57698A6ACCFA12B8');
        $this->addSql('ALTER TABLE documentation_entry DROP FOREIGN KEY FK_F5AEE4C7B03A8386');
        $this->addSql('ALTER TABLE documentation_entry DROP FOREIGN KEY FK_F5AEE4C7896DBBDE');
        $this->addSql('ALTER TABLE documentation_history DROP FOREIGN KEY FK_A5E75F23B03A8386');
        $this->addSql('ALTER TABLE documentation_task DROP FOREIGN KEY FK_6699A9C8B03A8386');
        $this->addSql('ALTER TABLE documentation_task DROP FOREIGN KEY FK_6699A9C8896DBBDE');
        $this->addSql('ALTER TABLE documentation_version DROP FOREIGN KEY FK_3D41FCABB03A8386');
        $this->addSql('ALTER TABLE documentation_version DROP FOREIGN KEY FK_3D41FCAB896DBBDE');
        $this->addSql('ALTER TABLE membership DROP FOREIGN KEY FK_86FFD285A76ED395');
        $this->addSql('ALTER TABLE manager DROP FOREIGN KEY FK_FA2425B9A76ED395');
        $this->addSql('ALTER TABLE role DROP FOREIGN KEY FK_57698A6AA76ED395');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649B03A8386');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649896DBBDE');
        $this->addSql('DROP TABLE actor');
        $this->addSql('DROP TABLE documentation_entry');
        $this->addSql('DROP TABLE documentation_folder');
        $this->addSql('DROP TABLE documentation_folder_permission');
        $this->addSql('DROP TABLE documentation_history');
        $this->addSql('DROP TABLE documentation_task');
        $this->addSql('DROP TABLE documentation_task_label');
        $this->addSql('DROP TABLE documentation_task_entries');
        $this->addSql('DROP TABLE documentation_task_permission');
        $this->addSql('DROP TABLE documentation_version');
        $this->addSql('DROP TABLE element');
        $this->addSql('DROP TABLE label');
        $this->addSql('DROP TABLE historic_period');
        $this->addSql('DROP TABLE membership');
        $this->addSql('DROP TABLE organization');
        $this->addSql('DROP TABLE manager');
        $this->addSql('DROP TABLE periodicity');
        $this->addSql('DROP TABLE profile');
        $this->addSql('DROP TABLE reference');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE user');
    }
}
