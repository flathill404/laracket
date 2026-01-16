<?php

use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\OrganizationMemberController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectMemberController;
use App\Http\Controllers\Api\ProjectTeamController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\TeamMemberController;
use App\Http\Controllers\Api\TicketAssigneeController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketOrderController;
use App\Http\Controllers\Api\TicketStatusController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserProjectController;
use App\Http\Controllers\Api\UserTeamController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // User Scope
    Route::get('/user', [UserController::class, 'show']);
    Route::get('/users/{user}/projects', [UserProjectController::class, 'index']);
    Route::get('/users/{user}/teams', [UserTeamController::class, 'index']);

    // Organization Scope
    Route::get('/organizations', [OrganizationController::class, 'index']);
    Route::post('/organizations', [OrganizationController::class, 'store']);
    Route::get('/organizations/{organization}', [OrganizationController::class, 'show']);
    Route::put('/organizations/{organization}', [OrganizationController::class, 'update']);
    Route::delete('/organizations/{organization}', [OrganizationController::class, 'destroy']);

    // Org Members (Sub)
    Route::get('/organizations/{organization}/members', [OrganizationMemberController::class, 'index']);
    Route::post('/organizations/{organization}/members', [OrganizationMemberController::class, 'store']);
    Route::patch('/organizations/{organization}/members/{user}', [OrganizationMemberController::class, 'update']);
    Route::delete('/organizations/{organization}/members/{user}', [OrganizationMemberController::class, 'destroy']);

    // Projects
    Route::get('/organizations/{organization}/projects', [ProjectController::class, 'index']);
    Route::post('/organizations/{organization}/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::put('/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

    // Project Sub-Resources
    Route::get('/projects/{project}/members', [ProjectMemberController::class, 'index']);
    Route::post('/projects/{project}/members', [ProjectMemberController::class, 'store']);
    Route::delete('/projects/{project}/members/{user}', [ProjectMemberController::class, 'destroy']);
    Route::post('/projects/{project}/teams', [ProjectTeamController::class, 'store']);
    Route::delete('/projects/{project}/teams/{team}', [ProjectTeamController::class, 'destroy']);

    // Teams
    Route::get('/organizations/{organization}/teams', [TeamController::class, 'index']);
    Route::post('/organizations/{organization}/teams', [TeamController::class, 'store']);
    Route::get('/teams/{team}', [TeamController::class, 'show']);
    Route::put('/teams/{team}', [TeamController::class, 'update']);
    Route::delete('/teams/{team}', [TeamController::class, 'destroy']);

    // Team Members (Sub)
    Route::get('/teams/{team}/members', [TeamMemberController::class, 'index']);
    Route::post('/teams/{team}/members', [TeamMemberController::class, 'store']);
    Route::patch('/teams/{team}/members/{user}', [TeamMemberController::class, 'update']);
    Route::delete('/teams/{team}/members/{user}', [TeamMemberController::class, 'destroy']);

    // Tickets
    Route::get('/projects/{project}/tickets', [TicketController::class, 'index']);
    Route::post('/projects/{project}/tickets', [TicketController::class, 'store']);
    Route::get('/tickets/{ticket}', [TicketController::class, 'show']);
    Route::put('/tickets/{ticket}', [TicketController::class, 'update']);
    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy']);

    // Ticket Sub-Resources
    Route::patch('/tickets/{ticket}/status', [TicketStatusController::class, 'update']);
    Route::patch('/tickets/{ticket}/order', [TicketOrderController::class, 'update']);
    Route::post('/tickets/{ticket}/assignees', [TicketAssigneeController::class, 'store']);
    Route::delete('/tickets/{ticket}/assignees/{user}', [TicketAssigneeController::class, 'destroy']);

    // Comments
    Route::get('/tickets/{ticket}/comments', [\App\Http\Controllers\Api\TicketCommentController::class, 'index']);
    Route::post('/tickets/{ticket}/comments', [\App\Http\Controllers\Api\TicketCommentController::class, 'store']);
});
