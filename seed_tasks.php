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
$priorities = [1, 2, 3, 4, 5];

// Insert 10 random tasks
for ($i = 0; $i < 10; $i++) {
    $title = $titles[array_rand($titles)];
    $description = $descriptions[array_rand($descriptions)];
    $completed = rand(0, 1) ? true : false; // PHP boolean
    $createdAt = (new DateTime())->format('Y-m-d H:i:s');
    $updatedAt = $completed ? (new DateTime())->modify('-' . rand(1, 5) . ' days')->format('Y-m-d H:i:s') : null;
    $priority = $priorities[array_rand($priorities)];

    $connection->executeStatement(
        'INSERT INTO tasks (title, description, completed, created_at, updated_at, priority) VALUES (:title, :description, :completed, :created_at, :updated_at, :priority)',
        [
            'title' => $title,
            'description' => $description,
            'completed' => $completed ? 'TRUE' : 'FALSE', // Explicitly cast to PostgreSQL boolean
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'priority' => $priority
        ]
    );
}

echo "Inserted 10 random tasks into the tasks table.\n";