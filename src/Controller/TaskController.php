<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\TaskStatus;
use App\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/tasks')]
class TaskController extends AbstractController
{
    public function __construct(
        private TaskService $taskService,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'task_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(Request $request): JsonResponse
    {
        try {
            $page = (int) $request->query->get('page', 1);
            $limit = (int) $request->query->get('limit', 10);
            $tasks = $this->taskService->findAll($page, $limit);
            return $this->json($tasks, Response::HTTP_OK, [], ['groups' => ['task:read']]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to fetch tasks'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'task_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(int $id): JsonResponse
    {
        try {
            $task = $this->taskService->findById($id);
            if (!$task) {
                return $this->json(['error' => 'Task not found'], Response::HTTP_NOT_FOUND);
            }
            return $this->json($task, Response::HTTP_OK, [], ['groups' => ['task:read']]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to fetch task'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: 'task_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true) ?? [];
            $task = new Task();
            $task->setTitle($data['title'] ?? 'Untitled');
            $task->setDescription($data['description'] ?? null);
            $task->setStatus(isset($data['status']) ? TaskStatus::from($data['status']) : TaskStatus::TODO);

            $errors = $this->validator->validate($task);
            if (count($errors) > 0) {
                return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
            }

            $task = $this->taskService->create($task);
            return $this->json($task, Response::HTTP_CREATED, [], ['groups' => ['task:read']]);
        } catch (\ValueError $e) {
            return $this->json(['error' => 'Invalid status value'], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to create task'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'task_update', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $task = $this->taskService->findById($id);
            if (!$task) {
                return $this->json(['error' => 'Task not found'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true) ?? [];
            $task->setTitle($data['title'] ?? $task->getTitle());
            $task->setDescription($data['description'] ?? $task->getDescription());
            if (isset($data['status'])) {
                $task->setStatus(TaskStatus::from($data['status']));
            }

            $errors = $this->validator->validate($task);
            if (count($errors) > 0) {
                return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
            }

            $task = $this->taskService->update($task);
            return $this->json($task, Response::HTTP_OK, [], ['groups' => ['task:read']]);
        } catch (\ValueError $e) {
            return $this->json(['error' => 'Invalid status value'], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to update task'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'task_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(int $id): JsonResponse
    {
        try {
            $task = $this->taskService->findById($id);
            if (!$task) {
                return $this->json(['error' => 'Task not found'], Response::HTTP_NOT_FOUND);
            }

            $this->taskService->delete($task);
            return $this->json(['message' => 'Task deleted'], Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to delete task'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}