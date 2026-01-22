## Project Overview

Laracket is a backend REST API for a Ticket Management SaaS platform built with Laravel 12.x and PHP 8.4. It provides multi-tenant support through organizations, with projects, teams, and issue tracking.

## Development Commands

```bash
# Initial setup (install deps, create .env, generate key, run migrations)
composer run setup

# Start development server
composer run dev

# Run tests (clears config cache first)
composer run test

# Run single test file
php artisan test tests/Feature/Api/TicketControllerTest.php

# Run single test method
php artisan test --filter=test_method_name

# Code formatting with Pint
composer run lint

# Static analysis with PHPStan (level 10)
composer run analyze

# Generate IDE helper files
composer run ide-helper-update

# Fresh database with demo data
composer run demo
```

## Architecture

### Action Pattern (`app/Actions/`)
Business logic is encapsulated in invokable Action classes organized by domain:
- Actions handle validation, authorization, and database transactions
- Each action is a single-purpose class with an `__invoke()` method
- Example: `CreateTicket`, `UpdateOrganization`, `AddTeamMember`

```php
class CreateTicket
{
    public function __invoke(User $creator, Project $project, array $input): Ticket
    {
        $validated = Validator::make($input, $this->rules())->validate();
        return DB::transaction(function () use ($project, $validated) {
            // ...
        });
    }
}
```

### Query Pattern (`app/Queries/`)
Complex database queries are extracted into reusable Query classes:
- Handle filtering, eager loading, and pagination
- Return Collections or Paginators
- Example: `GetProjectTickets`, `GetTeamMembers`, `GetUserProjects`

### Value Objects (`app/Values/`)
Immutable data structures for query parameters and data transfer:
- `TicketQuery`: Encapsulates ticket filtering, sorting, and pagination params
- `ActivityPayload`: Structured data for activity logging

### Enums (`app/Enums/`)
Type-safe enums for statuses and roles:
- `TicketStatus`: open, in_progress, in_review, resolved, closed
- `OrganizationRole`: owner, admin, member
- `TeamRole`: leader, member
- `TicketUserType`: assignee, reviewer

### Policies (`app/Policies/`)
Authorization logic using Laravel Gates:
- Controllers use `Gate::authorize()` before actions
- Policies map to models: `OrganizationPolicy`, `ProjectPolicy`, `TicketPolicy`, etc.

### Observers (`app/Observers/`)
Model lifecycle hooks for audit logging:
- `TicketObserver`: Tracks ticket creation and updates
- `CommentObserver`: Records comment metadata

## Key Patterns

### Controller Flow
Controllers delegate to Actions and Queries following CQS (Command Query Separation) principles:

**Create (store)**: Action returns the created resource
```php
public function store(Request $request, Project $project): TicketResource
{
    Gate::authorize('create', [Ticket::class, $project]);
    $ticket = (new CreateTicket)($request->user(), $project, $request->all());
    return new TicketResource($ticket);
}
```

**Update**: Action performs the mutation, Query fetches the response
```php
public function update(Request $request, Ticket $ticket, UpdateTicket $action, GetTicketDetail $query): TicketResource
{
    Gate::authorize('update', $ticket);
    $action($ticket, $request->all());
    $updatedTicket = $query($ticket);
    return new TicketResource($updatedTicket);
}
```

This separation ensures:
- Actions remain pure commands (write-only)
- Queries handle eager loading consistently with `show` methods
- Response structure is unified across `show` and `update` endpoints

### Middleware
- `HandleKeyInflection`: Converts snake_case requests to camelCase and vice versa for responses

### Polymorphic Relationships
- Projects can be assigned to Users or Teams (polymorphic many-to-many)
- Tickets have assignees and reviewers distinguished by `TicketUserType` enum

## Testing

- Framework: Pest PHP with Laravel plugin
- Tests use SQLite in-memory database
- Feature tests in `tests/Feature/` (Actions, API endpoints, Policies)
- Unit tests in `tests/Unit/` (Enums, Queries, Values, Middleware)

## Static Analysis

PHPStan is configured at level 10 (maximum strictness) with Larastan extension. All code must pass `composer run analyze`.
