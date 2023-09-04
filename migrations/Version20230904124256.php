<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230904124256 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commentaire (id INT AUTO_INCREMENT NOT NULL, commentateur_id INT NOT NULL, rencontre_id INT NOT NULL, date_heure DATETIME NOT NULL, texte VARCHAR(1024) NOT NULL, INDEX IDX_67F068BCD7428D7A (commentateur_id), INDEX IDX_67F068BC6CFC0818 (rencontre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE equipe (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, pays VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE joueur (id INT AUTO_INCREMENT NOT NULL, equipe_id INT DEFAULT NULL, numero SMALLINT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, INDEX IDX_FD71A9C56D861B89 (equipe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pari (id INT AUTO_INCREMENT NOT NULL, rencontre_id INT NOT NULL, equipe_id INT NOT NULL, user_id INT NOT NULL, date DATE NOT NULL, mise DOUBLE PRECISION NOT NULL, gain DOUBLE PRECISION DEFAULT NULL, INDEX IDX_2A091C1F6CFC0818 (rencontre_id), INDEX IDX_2A091C1F6D861B89 (equipe_id), INDEX IDX_2A091C1FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rencontre (id INT AUTO_INCREMENT NOT NULL, equipe_a_id INT NOT NULL, equipe_b_id INT NOT NULL, heure_debut DATETIME NOT NULL, heure_fin DATETIME NOT NULL, statut INT NOT NULL, score_equipe_a INT NOT NULL, score_equipe_b INT DEFAULT NULL, meteo VARCHAR(255) DEFAULT NULL, cote_equipe_a DOUBLE PRECISION NOT NULL, cote_equipe_b DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_460C35ED3297C2A6 (equipe_a_id), UNIQUE INDEX UNIQ_460C35ED20226D48 (equipe_b_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCD7428D7A FOREIGN KEY (commentateur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC6CFC0818 FOREIGN KEY (rencontre_id) REFERENCES rencontre (id)');
        $this->addSql('ALTER TABLE joueur ADD CONSTRAINT FK_FD71A9C56D861B89 FOREIGN KEY (equipe_id) REFERENCES equipe (id)');
        $this->addSql('ALTER TABLE pari ADD CONSTRAINT FK_2A091C1F6CFC0818 FOREIGN KEY (rencontre_id) REFERENCES rencontre (id)');
        $this->addSql('ALTER TABLE pari ADD CONSTRAINT FK_2A091C1F6D861B89 FOREIGN KEY (equipe_id) REFERENCES equipe (id)');
        $this->addSql('ALTER TABLE pari ADD CONSTRAINT FK_2A091C1FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE rencontre ADD CONSTRAINT FK_460C35ED3297C2A6 FOREIGN KEY (equipe_a_id) REFERENCES equipe (id)');
        $this->addSql('ALTER TABLE rencontre ADD CONSTRAINT FK_460C35ED20226D48 FOREIGN KEY (equipe_b_id) REFERENCES equipe (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCD7428D7A');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC6CFC0818');
        $this->addSql('ALTER TABLE joueur DROP FOREIGN KEY FK_FD71A9C56D861B89');
        $this->addSql('ALTER TABLE pari DROP FOREIGN KEY FK_2A091C1F6CFC0818');
        $this->addSql('ALTER TABLE pari DROP FOREIGN KEY FK_2A091C1F6D861B89');
        $this->addSql('ALTER TABLE pari DROP FOREIGN KEY FK_2A091C1FA76ED395');
        $this->addSql('ALTER TABLE rencontre DROP FOREIGN KEY FK_460C35ED3297C2A6');
        $this->addSql('ALTER TABLE rencontre DROP FOREIGN KEY FK_460C35ED20226D48');
        $this->addSql('DROP TABLE commentaire');
        $this->addSql('DROP TABLE equipe');
        $this->addSql('DROP TABLE joueur');
        $this->addSql('DROP TABLE pari');
        $this->addSql('DROP TABLE rencontre');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
