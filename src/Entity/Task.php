<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\Table(name: 'tasks')]
#[ORM\HasLifecycleCallbacks]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['task:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['task:read', 'task:write'])]
    #[Assert\NotBlank(message: 'Title cannot be empty')]
    #[Assert\Length(max: 255, maxMessage: 'Title cannot be longer than 255 characters')]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['task:read', 'task:write'])]
    #[Assert\Length(max: 255, maxMessage: 'Description cannot be longer than 255 characters')]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 255, enumType: TaskStatus::class)]
    #[Groups(['task:read', 'task:write'])]
    #[Assert\NotBlank(message: 'Status cannot be empty')]
    private TaskStatus $status;

    #[ORM\Column]
    #[Groups(['task:read'])]
    private ?\DateTimeImmutable $created_at = null;

    public function __construct()
    {
        $this->status = TaskStatus::TODO;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    public function setStatus(TaskStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtOnPrePersist(): void
    {
        if ($this->created_at === null) {
            $this->created_at = new \DateTimeImmutable();
        }
    }
}

enum TaskStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}