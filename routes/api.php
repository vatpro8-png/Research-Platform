<?php

use App\Http\Controllers\AiController;
use App\Http\Controllers\CitationController;
use App\Http\Controllers\IntegrationHealthController;
use App\Http\Controllers\PaperController;
use App\Http\Controllers\ProjectFeedbackController;
use App\Http\Controllers\ProjectMediaController;
use App\Http\Controllers\ProjectTaskController;
use App\Http\Controllers\ResearchProjectController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth')->group(function (): void {
    Route::get('/projects', [ResearchProjectController::class, 'index']);
    Route::post('/projects', [ResearchProjectController::class, 'store']);
    Route::get('/integrations/health', IntegrationHealthController::class);
    Route::get('/ai/status', [AiController::class, 'status']);
    Route::middleware('project.member')->group(function (): void {
        Route::get('/projects/{project}', [ResearchProjectController::class, 'show']);
        Route::post('/projects/{project}/versions', [ResearchProjectController::class, 'storeVersion']);
        Route::get('/projects/{project}/architecture/audit', [ResearchProjectController::class, 'auditArchitecture']);
        Route::patch('/projects/{project}/setup', [ResearchProjectController::class, 'updateSetup']);
        Route::patch('/projects/{project}/formatting', [ResearchProjectController::class, 'updateFormatting']);
        Route::post('/projects/{project}/presence', [ResearchProjectController::class, 'updatePresence']);
        Route::post('/projects/{project}/members', [ResearchProjectController::class, 'addMember']);
        Route::delete('/projects/{project}/members/{user}', [ResearchProjectController::class, 'removeMember']);
        Route::get('/projects/{project}/citations', [CitationController::class, 'index']);
        Route::get('/projects/{project}/citations/export', [CitationController::class, 'export']);
        Route::post('/projects/{project}/citations', [CitationController::class, 'store']);
        Route::delete('/projects/{project}/citations/{citation}', [CitationController::class, 'destroy']);
        Route::get('/projects/{project}/media', [ProjectMediaController::class, 'index']);
        Route::post('/projects/{project}/media', [ProjectMediaController::class, 'store']);
        Route::delete('/projects/{project}/media/{media}', [ProjectMediaController::class, 'destroy']);
        Route::get('/projects/{project}/tasks', [ProjectTaskController::class, 'index']);
        Route::post('/projects/{project}/tasks', [ProjectTaskController::class, 'store']);
        Route::get('/projects/{project}/feedback', [ProjectFeedbackController::class, 'index']);
        Route::post('/projects/{project}/feedback', [ProjectFeedbackController::class, 'store']);
        Route::post('/projects/{project}/ai/summary', [AiController::class, 'summarize'])->middleware('ai.access');
        Route::post('/projects/{project}/ai/evaluate', [AiController::class, 'evaluate'])->middleware('ai.access');
        Route::post('/projects/{project}/ai/methodology-matrix', [AiController::class, 'recommendMethodologyMatrix'])->middleware('ai.access');
        Route::get('/projects/{project}/papers', [PaperController::class, 'index']);
        Route::post('/projects/{project}/papers', [PaperController::class, 'store']);
    });
    Route::patch('/tasks/{task}', [ProjectTaskController::class, 'update']);
    Route::patch('/feedback/{feedback}', [ProjectFeedbackController::class, 'update']);
});
