<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Task;
use App\Entity\TaskStatus;
use App\Repository\TaskRepository;
use App\Service\TaskService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class TaskServiceTest extends TestCase
{
    private TaskRepository $taskRepository;

    private ArrayAdapter $cache;

    private TaskService $taskService;

    protected function setUp(): void
    {
        $this->taskRepository = $this->createMock(TaskRepository::class);
        $this->cache = new ArrayAdapter();
        $this->taskService = new TaskService($this->taskRepository, $this->cache);
    }

    public function testFindAll(): void
    {
        $task1 = new Task();
        $task1->setTitle('Task 1');
        $task1->setDescription('First task');
        $task1->setStatus(TaskStatus::TODO);

        $task2 = new Task();
        $task2->setTitle('Task 2');
        $task2->setDescription('Second task');
        $task2->setStatus(TaskStatus::IN_PROGRESS);

        $expectedTasks = [$task1, $task2];

        $this->taskRepository
            ->expects($this->once())
            ->method('findAllPaginated')
            ->with(1, 10)
            ->willReturn($expectedTasks);

        $tasks = $this->taskService->findAll(1, 10);

        // Use assertEquals instead of assertSame to compare properties
        $this->assertEquals($expectedTasks, $tasks);

        // Test cache hit (no additional repository call)
        $cachedTasks = $this->taskService->findAll(1, 10);
        $this->assertEquals($expectedTasks, $cachedTasks);
    }

    public function testFindById(): void
    {
        $task = new Task();
        $task->setTitle('Task 1');
        $task->setDescription('First task');
        $task->setStatus(TaskStatus::TODO);

        $this->taskRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($task);

        $foundTask = $this->taskService->findById(1);

        $this->assertSame($task, $foundTask);

        // Test cache hit
        $cachedTask = $this->taskService->findById(1);
        $this->assertEquals($task, $cachedTask);
    }

    public function testCreate(): void
    {
        $task = new Task();
        $task->setTitle('New Task');
        $task->setDescription('Description');
        $task->setStatus(TaskStatus::TODO);

        $this->taskRepository
            ->expects($this->once())
            ->method('save')
            ->with($task, true);

        $createdTask = $this->taskService->create($task);

        $this->assertSame($task, $createdTask);
        $this->assertEquals('New Task', $createdTask->getTitle());
        $this->assertEquals('Description', $createdTask->getDescription());
        $this->assertEquals(TaskStatus::TODO, $createdTask->getStatus());
    }

    public function testUpdate(): void
    {
        $task = new Task();
        $task->setTitle('Original Title');
        $task->setDescription('Original Description');
        $task->setStatus(TaskStatus::TODO);

        // Simulate an update
        $task->setTitle('Updated Title');
        $task->setDescription('Updated Description');
        $task->setStatus(TaskStatus::IN_PROGRESS);

        $this->taskRepository
            ->expects($this->once())
            ->method('save')
            ->with($task, true);

        $updatedTask = $this->taskService->update($task);

        $this->assertSame($task, $updatedTask);
        $this->assertEquals('Updated Title', $updatedTask->getTitle());
        $this->assertEquals('Updated Description', $updatedTask->getDescription());
        $this->assertEquals(TaskStatus::IN_PROGRESS, $updatedTask->getStatus());
    }

    public function testDelete(): void
    {
        $task = new Task();
        $task->setTitle('Task to delete');
        $task->setStatus(TaskStatus::DONE);

        $this->taskRepository
            ->expects($this->once())
            ->method('remove')
            ->with($task, true);

        $this->taskService->delete($task);
    }
}
