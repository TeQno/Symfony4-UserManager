<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181123224227 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE keyword (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(255) NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(255) NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sale (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, uuid VARCHAR(255) NOT NULL, date_time DATETIME NOT NULL, INDEX IDX_E54BC005A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, price_excluding_taxes DOUBLE PRECISION NOT NULL, price_including_taxes DOUBLE PRECISION NOT NULL, taxes DOUBLE PRECISION NOT NULL, stock INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_keyword (product_id INT NOT NULL, keyword_id INT NOT NULL, INDEX IDX_8B4A6A8B4584665A (product_id), INDEX IDX_8B4A6A8B115D4552 (keyword_id), PRIMARY KEY(product_id, keyword_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_category (product_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_CDFC73564584665A (product_id), INDEX IDX_CDFC735612469DE2 (category_id), PRIMARY KEY(product_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE country (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(255) NOT NULL, libelle VARCHAR(255) NOT NULL, iso31661alpha2 VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE uuid (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE unique_sale (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, sale_id INT NOT NULL, uuid VARCHAR(255) NOT NULL, quantity INT NOT NULL, price_excluding_taxes DOUBLE PRECISION NOT NULL, price_including_taxes DOUBLE PRECISION NOT NULL, taxes DOUBLE PRECISION NOT NULL, is_paid TINYINT(1) NOT NULL, is_cancelled TINYINT(1) NOT NULL, is_send TINYINT(1) NOT NULL, is_received TINYINT(1) NOT NULL, INDEX IDX_23C3FB094584665A (product_id), INDEX IDX_23C3FB094A7E4868 (sale_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bank_account (id INT AUTO_INCREMENT NOT NULL, country_id INT NOT NULL, iban VARCHAR(255) NOT NULL, account_name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, post_code VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, uuid VARCHAR(255) NOT NULL, INDEX IDX_53A23E0AF92F3E70 (country_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, b64data LONGTEXT NOT NULL, src VARCHAR(255) NOT NULL, alt VARCHAR(255) NOT NULL, INDEX IDX_C53D045F4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sale ADD CONSTRAINT FK_E54BC005A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product_keyword ADD CONSTRAINT FK_8B4A6A8B4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_keyword ADD CONSTRAINT FK_8B4A6A8B115D4552 FOREIGN KEY (keyword_id) REFERENCES keyword (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_category ADD CONSTRAINT FK_CDFC73564584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_category ADD CONSTRAINT FK_CDFC735612469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE unique_sale ADD CONSTRAINT FK_23C3FB094584665A FOREIGN KEY (product_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE unique_sale ADD CONSTRAINT FK_23C3FB094A7E4868 FOREIGN KEY (sale_id) REFERENCES sale (id)');
        $this->addSql('ALTER TABLE bank_account ADD CONSTRAINT FK_53A23E0AF92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE user ADD uuid VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product_keyword DROP FOREIGN KEY FK_8B4A6A8B115D4552');
        $this->addSql('ALTER TABLE product_category DROP FOREIGN KEY FK_CDFC735612469DE2');
        $this->addSql('ALTER TABLE unique_sale DROP FOREIGN KEY FK_23C3FB094A7E4868');
        $this->addSql('ALTER TABLE product_keyword DROP FOREIGN KEY FK_8B4A6A8B4584665A');
        $this->addSql('ALTER TABLE product_category DROP FOREIGN KEY FK_CDFC73564584665A');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F4584665A');
        $this->addSql('ALTER TABLE bank_account DROP FOREIGN KEY FK_53A23E0AF92F3E70');
        $this->addSql('DROP TABLE keyword');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE sale');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_keyword');
        $this->addSql('DROP TABLE product_category');
        $this->addSql('DROP TABLE country');
        $this->addSql('DROP TABLE uuid');
        $this->addSql('DROP TABLE unique_sale');
        $this->addSql('DROP TABLE bank_account');
        $this->addSql('DROP TABLE image');
        $this->addSql('ALTER TABLE user DROP uuid');
    }
}
