<?php

declare(strict_types=1);

use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\OrganizationMemberController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectMemberController;
use App\Http\Controllers\Api\ProjectTeamController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\TeamMemberController;
use App\Http\Controllers\Api\TeamTicketsController;
use App\Http\Controllers\Api\TicketActivityController;
use App\Http\Controllers\Api\TicketAssigneeController;
use App\Http\Controllers\Api\TicketCommentController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketOrderController;
use App\Http\Controllers\Api\TicketReviewerController;
use App\Http\Controllers\Api\TicketSearchController;
use App\Http\Controllers\Api\TicketStatusController;
use App\Http\Controllers\Api\UserAvatarController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserProjectController;
use App\Http\Controllers\Api\UserTeamController;
use App\Http\Controllers\Api\UserTicketsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // User Scope
    Route::get('/user', [UserController::class, 'show']);
    Route::post('/user/avatar', [UserAvatarController::class, 'update']);
    Route::delete('/user/avatar', [UserAvatarController::class, 'destroy']);
    Route::get('/users/{user}/projects', [UserProjectController::class, 'index']);
    Route::get('/users/{user}/teams', [UserTeamController::class, 'index']);
    Route::get('/users/{user}/tickets', [UserTicketsController::class, 'index']);

    // Organization Scope
    Route::prefix('organizations/{organization}')->group(function () {
        Route::get('/', [OrganizationController::class, 'show']);
        Route::put('/', [OrganizationController::class, 'update']);
        Route::delete('/', [OrganizationController::class, 'destroy']);

        // Organization Sub-Resources
        Route::get('/members', [OrganizationMemberController::class, 'index']);
        Route::post('/members', [OrganizationMemberController::class, 'store']);
        Route::patch('/members/{user}', [OrganizationMemberController::class, 'update']);
        Route::delete('/members/{user}', [OrganizationMemberController::class, 'destroy']);

        // Projects (Scoped)
        Route::get('/projects', [ProjectController::class, 'index']);
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::scopeBindings()->group(function () {
            Route::get('/projects/{project}', [ProjectController::class, 'show']);
            Route::put('/projects/{project}', [ProjectController::class, 'update']);
            Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

            // Project Sub-Resources
            Route::get('/projects/{project}/members', [ProjectMemberController::class, 'index']);
            Route::post('/projects/{project}/members', [ProjectMemberController::class, 'store']);
            Route::delete('/projects/{project}/members/{user}', [ProjectMemberController::class, 'destroy']);
            Route::post('/projects/{project}/teams', [ProjectTeamController::class, 'store']);
            Route::delete('/projects/{project}/teams/{team}', [ProjectTeamController::class, 'destroy']);

            // Tickets (Scoped)
            Route::get('/projects/{project}/tickets', [TicketController::class, 'index']);
            Route::post('/projects/{project}/tickets', [TicketController::class, 'store']);
            Route::get('/projects/{project}/tickets/{ticket}', [TicketController::class, 'show']);
            Route::put('/projects/{project}/tickets/{ticket}', [TicketController::class, 'update']);
            Route::delete('/projects/{project}/tickets/{ticket}', [TicketController::class, 'destroy']);
        });

        // Teams (Scoped)
        Route::get('/teams', [TeamController::class, 'index']);
        Route::post('/teams', [TeamController::class, 'store']);
        Route::scopeBindings()->group(function () {
            Route::get('/teams/{team}', [TeamController::class, 'show']);
            Route::put('/teams/{team}', [TeamController::class, 'update']);
            Route::delete('/teams/{team}', [TeamController::class, 'destroy']);

            // Team Sub-Resources
            Route::get('/teams/{team}/members', [TeamMemberController::class, 'index']);
            Route::post('/teams/{team}/members', [TeamMemberController::class, 'store']);
            Route::patch('/teams/{team}/members/{user}', [TeamMemberController::class, 'update']);
            Route::delete('/teams/{team}/members/{user}', [TeamMemberController::class, 'destroy']);
            Route::get('/teams/{team}/tickets', [TeamTicketsController::class, 'index']);
        });
    });

    // Organizations
    Route::get('/organizations', [OrganizationController::class, 'index']);
    Route::post('/organizations', [OrganizationController::class, 'store']);

    // Tickets
    Route::get('/projects/{project}/tickets', [TicketController::class, 'index']);
    Route::post('/projects/{project}/tickets', [TicketController::class, 'store']);
    Route::get('/tickets/search', [TicketSearchController::class, 'index']);
    Route::get('/tickets/{ticket}', [TicketController::class, 'show']);
    Route::put('/tickets/{ticket}', [TicketController::class, 'update']);
    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy']);

    // Ticket Sub-Resources
    Route::patch('/tickets/{ticket}/status', [TicketStatusController::class, 'update']);
    Route::patch('/tickets/{ticket}/order', [TicketOrderController::class, 'update']);
    Route::post('/tickets/{ticket}/assignees', [TicketAssigneeController::class, 'store']);
    Route::delete('/tickets/{ticket}/assignees/{user}', [TicketAssigneeController::class, 'destroy']);
    Route::post('/tickets/{ticket}/reviewers', [TicketReviewerController::class, 'store']);
    Route::delete('/tickets/{ticket}/reviewers/{user}', [TicketReviewerController::class, 'destroy']);

    // Comments
    Route::get('/tickets/{ticket}/comments', [TicketCommentController::class, 'index']);
    Route::post('/tickets/{ticket}/comments', [TicketCommentController::class, 'store']);

    // Activities
    Route::get('/tickets/{ticket}/activities', [TicketActivityController::class, 'index']);
});
