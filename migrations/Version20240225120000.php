<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240225120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tasks table matching updated Task entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tasks (
            id SERIAL PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description VARCHAR(255) DEFAULT NULL,
            status VARCHAR(255) NOT NULL DEFAULT \'todo\',
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            CONSTRAINT status_check CHECK (status IN (\'todo\', \'in_progress\', \'done\'))
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tasks');
    }
}