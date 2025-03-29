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
  - Copies application code and sets ownership to `www-data`.
- **Command**: Runs `php-fpm` to serve the API.

### Docker Compose
The `docker-compose.yml` orchestrates the multi-container setup.

- **Services**:
  - **app**: PHP-FPM container built from `Dockerfile`, exposing port `9000`, linked to `nginx`.
  - **nginx**: Web server using `nginx:1.25`, serving the API on port `8080`, with custom config.
  - **db**: PostgreSQL 16 database, with persistent data in a volume, port `5432`.
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

