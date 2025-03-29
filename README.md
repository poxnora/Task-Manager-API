# Task Manager API

### Overview
A RESTful Task Manager API built with PHP 8.2, Symfony 7, and JWT authentication, designed to manage tasks securely. Uses Roadrunner as an application server. This project leverages Docker for containerization and GitHub Actions for CI/CD and Azure for cloud deployment.

---

## Docker, CI/CD, Azure

### Dockerfile
The `Dockerfile` sets up the PHP environment for the API.

- **Base Image**: Uses `php:8.2-fpm` as the foundation.
- **Dependencies**:
  - Installs system packages: `git`, `curl`, `libpq-dev`, etc.
  - Adds PHP extensions: `pdo`, `pdo_pgsql`, `intl`.
- **Composer**: Installs Composer and project dependencies via `composer install`.
- **Configuration**:
  - Sets working directory to `/app`.
  - Copies application code.
- **Command**: Runs `wait-for-postgress.sh` and `seed-sql.sh` to populate DB and `rr serve` to start the API.

### Docker Compose
The `docker-compose.yml` orchestrates the multi-container setup.

- **Services**:
  - **app**: App running on 8080
  - **redis**: Redis serving on 6379
  - **postgres**: PostgreSQL database, with persistent data in a volume, port 5432.
- **Networking**: All services share a `task-manager-network` bridge network.
- **Volumes**: Persistent storage for PostgreSQL data (`db-data`).

### GitHub Pipeline
The `.github/workflows/pipeline.yml` defines a CI/CD pipeline.

- **Steps**:
  - **test**: Run tests and code checks
  - **build-and-push**: Build app and push to ACR if specified
  - **deploy-to-azure**: Create infrastrucutre on Azure and deploy app to Azure's Container Apps (optional)
 
### Bicep
The `infra.bicep` creates infrastrucutre on Azure

---

## JWT Authentication
Integration of **JSON Web Token (JWT)** authentication using the LexikJWTAuthenticationBundle to secure API endpoints. Key features include:
- **Token Generation**: Users authenticate via `/api/login_check` with credentials `test@example.com`, `test`, receiving a JWT token.
- **Protected Endpoints**: All `/api/*` routes require a `Bearer` token in the `Authorization` header.
- **Implementation**: Added in the `jwt-code-features` branch to enhance security, ensuring only authenticated users can access task management functionality.

---

## Controllers changes

### Class Structure
- **Old**: Standalone class with manually declared properties.
- **New**: Extends `AbstractController`, leveraging Symfony's built-in features (e.g., `$this->json()`).

### Pagination support
- **Old**: Simple `findAll()` with no pagination.
- **New**: Adds pagination with `$page` and `$limit` query parameters, calling `findAll($page, $limit)`.

### JWT security
- **Old**: No security attributes.
- **New**: Adds `#[IsGranted('ROLE_USER')]` to all methods for role-based access control.

### Better Error Handling
- **Old**: Minimal error handling (only validation errors).
- **New**: Comprehensive try-catch blocks for general exceptions and specific `\ValueError` (e.g., invalid status).

---

## Task Entity Changes

### Annotations and Attributes
- **Old**: Uses basic Doctrine ORM annotations (`#[ORM\Entity]`, `#[ORM\Column]`) without additional features.
- **New**: Adds Symfony Serializer (`#[Groups]`) and Validator (`#[Assert]`) annotations for serialization and validation.
.
### Status Handling
- **Old**: Uses a boolean `completed` field to track task status.
- **New**: Introduces a `TaskStatus` enum (`TODO`, `IN_PROGRESS`, `DONE`) for more granular status tracking.

### Validation
- **Old**: No validation constraints.
- **New**: Adds validation:
  - `title`: Must not be blank, max 255 characters.
  - `description`: Max 255 characters.
  - `status`: Must not be blank.

### Enum Definition
- **Old**: No enum usage.
- **New**: Adds `TaskStatus` enum with values `TODO`, `IN_PROGRESS`, `DONE` and a `values()` method for convenience.

