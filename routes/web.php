<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\QuestStepController;
use App\Http\Controllers\Admin\RouteController as GameRouteController;
use App\Http\Controllers\Admin\SubmissionApprovalController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Player\PlayController;
use App\Http\Controllers\PlayerLandingController;
use Illuminate\Support\Facades\Route;

Route::view('/locatie-test', 'player.location-test')->name('location.test');

Route::post('/groepen', [\App\Http\Controllers\Player\GroupRegistrationController::class, 'store'])->name('groups.register');
Route::get('/', [PlayerLandingController::class, 'index'])->name('landing');
Route::get('/join/{token}', [PlayerLandingController::class, 'join'])->name('join.with-token');
Route::post('/join/{token}', [PlayController::class, 'join'])->name('join.start');
Route::get('/play', [PlayController::class, 'dashboard'])->name('play.dashboard');
Route::get('/play/submit', [PlayController::class, 'submitRedirect'])->name('play.submit.redirect');
Route::post('/play/submit', [PlayController::class, 'submit'])->name('play.submit');
Route::post('/play/location', [PlayController::class, 'updateLocation'])->name('play.location');
Route::post('/play/location/lock', [PlayController::class, 'lockLocation'])->name('play.location.lock');
Route::post('/play/timeout/tick', [PlayController::class, 'tickTimeout'])->name('play.timeout.tick');
Route::post('/play/timeout/pause', [PlayController::class, 'pauseTimeout'])->name('play.timeout.pause');
Route::get('/play/live', [PlayController::class, 'live'])->name('play.live');
Route::post('/play/leave', [PlayController::class, 'leave'])->name('play.leave');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'show'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'store'])->name('login.store');
    Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');

    Route::middleware('admin.auth')->group(function (): void {
        Route::get('/inzendingen/{submission}/foto', function (\App\Models\StepSubmission $submission) {
            abort_unless($submission->photo_path, 404);
            return response()->file(\Illuminate\Support\Facades\Storage::disk('local')->path($submission->photo_path));
        })->name('submissions.photo');
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/games/{path?}', fn () => redirect()->route('admin.dashboard'))->where('path', '.*');
        Route::get('/berichten', fn () => redirect()->route('admin.dashboard'));
        Route::get('/live', [AdminDashboardController::class, 'live'])->name('dashboard.live');
        Route::get('/goedkeuringen', [SubmissionApprovalController::class, 'index'])->name('submissions.pending');
        Route::get('/goedkeuringen/live', [SubmissionApprovalController::class, 'live'])->name('submissions.pending.live');

        Route::put('/routes/{route}/startlocatie', [GameRouteController::class, 'assignStart'])->name('routes.start');
        Route::resource('routes', GameRouteController::class);
        Route::put('routes/{route}/quest-steps', [GameRouteController::class, 'updateQuestSteps'])
            ->name('routes.quest-steps.update');
        Route::post('routes/{route}/regenerate-token', [GameRouteController::class, 'regenerateToken'])
            ->name('routes.regenerate-token');
        Route::get('routes/{route}/qr', [GameRouteController::class, 'qr'])
            ->name('routes.qr');
        Route::get('routes/{route}/live', [GameRouteController::class, 'live'])
            ->name('routes.live');
        Route::post('routes/regenerate-all-tokens', [GameRouteController::class, 'regenerateAllTokens'])
            ->name('routes.regenerate-all-tokens');
        Route::post('routes/{route}/submissions/{submission}/approve', [GameRouteController::class, 'approveSubmission'])
            ->name('routes.submissions.approve');
        Route::post('routes/{route}/submissions/{submission}/reject', [GameRouteController::class, 'rejectSubmission'])
            ->name('routes.submissions.reject');
        Route::post('routes/{route}/messages', [GameRouteController::class, 'postMessage'])
            ->name('routes.messages.post');
        Route::resource('quest-steps', QuestStepController::class);
    });
});
