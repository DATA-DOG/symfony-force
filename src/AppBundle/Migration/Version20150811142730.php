<?php

namespace AppBundle\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150811142730 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE audit_associations (id INT AUTO_INCREMENT NOT NULL, typ VARCHAR(128) NOT NULL, tbl VARCHAR(128) NOT NULL, `label` VARCHAR(255) DEFAULT NULL, fk VARCHAR(255) NOT NULL, class VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE audit_logs (id INT AUTO_INCREMENT NOT NULL, source_id INT NOT NULL, target_id INT DEFAULT NULL, blame_id INT DEFAULT NULL, action VARCHAR(12) NOT NULL, tbl VARCHAR(128) NOT NULL, diff LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', logged_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_D62F2858953C1C61 (source_id), UNIQUE INDEX UNIQ_D62F2858158E0B66 (target_id), UNIQUE INDEX UNIQ_D62F28588C082A2E (blame_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, salt VARCHAR(48) NOT NULL, firstname VARCHAR(64) DEFAULT NULL, lastname VARCHAR(64) DEFAULT NULL, confirmation_token VARCHAR(48) DEFAULT NULL, roles INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, password VARCHAR(64) DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_fixtures (name VARCHAR(255) NOT NULL, PRIMARY KEY(name)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mail_templates (id INT AUTO_INCREMENT NOT NULL, alias VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_17F263EDE16C6B94 (alias), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE audit_logs ADD CONSTRAINT FK_D62F2858953C1C61 FOREIGN KEY (source_id) REFERENCES audit_associations (id)');
        $this->addSql('ALTER TABLE audit_logs ADD CONSTRAINT FK_D62F2858158E0B66 FOREIGN KEY (target_id) REFERENCES audit_associations (id)');
        $this->addSql('ALTER TABLE audit_logs ADD CONSTRAINT FK_D62F28588C082A2E FOREIGN KEY (blame_id) REFERENCES audit_associations (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE audit_logs DROP FOREIGN KEY FK_D62F2858953C1C61');
        $this->addSql('ALTER TABLE audit_logs DROP FOREIGN KEY FK_D62F2858158E0B66');
        $this->addSql('ALTER TABLE audit_logs DROP FOREIGN KEY FK_D62F28588C082A2E');
        $this->addSql('DROP TABLE audit_associations');
        $this->addSql('DROP TABLE audit_logs');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE app_fixtures');
        $this->addSql('DROP TABLE mail_templates');
    }
}
