# Laracket API

Laracket is a robust backend API designed for a Ticket Management SaaS platform. It provides comprehensive features for managing organizations, projects, teams, and issue tracking tickets.

## 🚀 Features

- **Organization Management**: Multi-tenant support allowing users to belong to organizations.
- **Project Tracking**: Create and manage projects with deadlines and summaries.
- **Team Collaboration**: Flexible team structures with roles (Leader/Member).
- **Ticket System**: Full lifecycle issue tracking (Open, In Progress, In Review, Done) with assignees and reviewers.
- **Polymorphic Assignments**: flexible assignment system for projects.
- **Secure API**: Authentication provided by Laravel Sanctum.

## 🛠 Tech Stack

- **PHP**: ^8.2
- **Framework**: Laravel 12.x
- **Database**: MySQL 8.4
- **Environment**: Docker & DevContainers

## 💻 Getting Started

### Prerequisites

- Docker Desktop
- VS Code (Recommended for DevContainer support)

### Installation

1. **Clone the repository**

   ```bash
   git clone <repository-url>
   cd laracket
    ```

2. **Start the Environment**

    This project is configured with DevContainers.

    - **VS Code**: Open the folder and click "Reopen in Container".

    - **Manual Docker**:

        ```bash
        docker compose up -d
        docker compose exec app bash
        ```

3. **Setup Application**
Run the convenient setup script included in `composer.json`. This command installs dependencies, sets up the `.env` file, generates the app key, and runs database migrations.

    ```bash
    composer run setup
    ```

### 🏃‍♂️ Running Local Development

To start the server, queue worker, and other services:

```bash
composer run dev
```

This will run `php artisan serve`, queue listeners, and other dev tools concurrently.

## 🗄️ Database Schema Overview

- **Organizations**: Root entity.
- **Teams**: Belongs to an Organization.
- **Projects**: Belongs to an Organization. Can be assigned to various entities.
- **Tickets**: Belongs to a Project. Contains status, deadline, and user assignments (Assignee/Reviewer).
