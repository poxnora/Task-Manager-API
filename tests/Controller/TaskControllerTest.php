<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private $client;

    private $entityManager;

    private $token;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();

        // Generate JWT token for the seeded user
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy([
            'email' => 'test@example.com',
        ]);
        if (! $user) {
            throw new \Exception('Seeded user not found. Run php seed_sql.php first.');
        }

        $jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        $this->token = $jwtManager->create($user);

        // Set default Authorization header for all requests
        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $this->token));
    }

    protected function tearDown(): void
    {
        $this->markTestSkipped();
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testCreateTask(): void
    {
        $this->markTestSkipped();
        $this->client->request(
            'POST',
            '/api/tasks',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'title' => 'Test Task',
                'description' => 'Test Description',
                'status' => 'todo', // Matches TaskStatus::TODO value
            ])
        );

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testGetTasks(): void
    {
        $this->markTestSkipped();
        $this->client->request('GET', '/api/tasks');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testGetTask(): void
    {
        // Use seeded Task 1
        $this->markTestSkipped();
        $task = $this->entityManager->getRepository('App\Entity\Task')->findOneBy([
            'title' => 'Task 1',
        ]);
        $this->assertNotNull($task, 'Seeded task "Task 1" not found. Run php seed_sql.php first.');

        $this->client->request('GET', '/api/tasks/' . $task->getId());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testUpdateTask(): void
    {
        $this->markTestSkipped();
        $task = $this->entityManager->getRepository('App\Entity\Task')->findOneBy([
            'title' => 'Task 1',
        ]);
        $this->assertNotNull($task, 'Seeded task "Task 1" not found. Run php seed_sql.php first.');

        $this->client->request(
            'PUT',
            '/api/tasks/' . $task->getId(),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode([
                'title' => 'Updated Task',
                'description' => 'Updated Description',
                'status' => 'in_progress', // Matches TaskStatus::IN_PROGRESS value
            ])
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testDeleteTask(): void
    {
        $this->markTestSkipped();
        $task = $this->entityManager->getRepository('App\Entity\Task')->findOneBy([
            'title' => 'Task 1',
        ]);
        $this->assertNotNull($task, 'Seeded task "Task 1" not found. Run php seed_sql.php first.');

        $this->client->request('DELETE', '/api/tasks/' . $task->getId());
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
    }
}
