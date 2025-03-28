<?php

require __DIR__.'/vendor/autoload.php';

use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$dbUrl = getenv('DATABASE_URL');
$connection = DriverManager::getConnection(['url' => $dbUrl]);

// Sample data for random tasks
$titles = [
    'Finish report', 'Call client', 'Review code', 'Plan meeting', 'Update documentation',
    'Test feature', 'Fix bug', 'Deploy app', 'Research API', 'Write tests'
];
$descriptions = [
    'High priority task', 'Routine check-in', 'Needs attention', null, 'Low urgency',
    'Technical task', 'Bug reported yesterday', 'Production release', 'Explore options', 'Unit tests'
];
$statuses = ['todo', 'in_progress', 'done'];

// Insert 10 random tasks
for ($i = 0; $i < 10; $i++) {
    $title = $titles[array_rand($titles)];
    $description = $descriptions[array_rand($descriptions)];
    $status = $statuses[array_rand($statuses)];
    $createdAt = (new DateTime())->format('Y-m-d H:i:s');

    $connection->executeStatement(
        'INSERT INTO tasks (title, description, status, created_at) VALUES (:title, :description, :status, :created_at)',
        [
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'created_at' => $createdAt,
        ]
    );
}
$connection->executeStatement(
    'INSERT INTO users (id, email, roles, password) 
     SELECT :id, :email, :roles, :password
     WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = :email)',
    [
        'id' => 1,
        'email' => 'test@example.com',
        'roles' => json_encode(['ROLE_USER']), 
        'password' => '$2y$13$SZ8WzskPNukoDB4xzddjMOU1dRuqjiic85Fsm03FqTW4a/Cits0Sa',
    ]
);
echo "Inserted 10 random tasks into the tasks table.\n";