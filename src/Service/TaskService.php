<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Task;
use App\Entity\TaskStatus;
use App\Repository\TaskRepository;
use Psr\Cache\CacheItemPoolInterface;

class TaskService
{
    private const CACHE_KEY_ALL_TASKS = 'all_tasks';
    private const CACHE_KEY_TASK_PREFIX = 'task_';
    private const CACHE_TTL = 3600; // 1 hour

    public function __construct(
        private TaskRepository $taskRepository,
        private CacheItemPoolInterface $cache
    ) {
    }

    /**
     * @return Task[]
     */
    public function findAll(int $page = 1, int $limit = 10): array
    {
        $cacheKey = self::CACHE_KEY_ALL_TASKS . "_p{$page}_l{$limit}";
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $tasks = $this->taskRepository->findAllPaginated($page, $limit);

        $cacheItem->set($tasks);
        $cacheItem->expiresAfter(self::CACHE_TTL);
        $this->cache->save($cacheItem);

        return $tasks;
    }

    public function findById(int $id): ?Task
    {
        $cacheKey = self::CACHE_KEY_TASK_PREFIX . $id;
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $task = $this->taskRepository->find($id);

        if ($task) {
            $cacheItem->set($task);
            $cacheItem->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cacheItem);
        }

        return $task;
    }

    public function create(Task $task): Task
    {
        $this->taskRepository->save($task, true);
        $this->invalidateCache();

        return $task;
    }

    public function update(Task $task): Task
    {
        $this->taskRepository->save($task, true);
        $this->invalidateCache($task->getId());

        return $task;
    }

    public function delete(Task $task): void
    {
        $this->taskRepository->remove($task, true);
        $this->invalidateCache($task->getId());
    }

    private function invalidateCache(?int $taskId = null): void
    {
        // Invalidate all paginated caches (simplified approach, could be optimized)
        $this->cache->clear(self::CACHE_KEY_ALL_TASKS); // Clears all items with prefix

        if ($taskId) {
            $this->cache->deleteItem(self::CACHE_KEY_TASK_PREFIX . $taskId);
        }
    }
}