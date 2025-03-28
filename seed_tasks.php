<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Symfony\Component\Dotenv\Dotenv;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

// Get database connection
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

echo "Inserted 10 random tasks into the tasks table.\n";