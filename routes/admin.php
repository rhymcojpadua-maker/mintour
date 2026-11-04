<?php

use App\Http\Controllers\Admin\AccommodationController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DestinationController;
use App\Http\Controllers\Admin\EmergencyContactController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\KnowledgeArticleController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UnansweredQuestionController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::patch('/destinations/{destination}/toggle-featured', [DestinationController::class, 'toggleFeatured'])
    ->name('destinations.toggle-featured');
Route::delete('/destinations/{destination}/photos/{photo}', [DestinationController::class, 'deletePhoto'])
    ->name('destinations.photos.destroy');
Route::resource('destinations', DestinationController::class)->except('show');
Route::resource('categories', CategoryController::class)->except('show');
Route::resource('accommodations', AccommodationController::class)->except('show');
Route::resource('restaurants', RestaurantController::class)->except('show');
Route::resource('products', ProductController::class)->except('show');
Route::resource('events', EventController::class)->except('show');
Route::resource('announcements', AnnouncementController::class)->except('show');
Route::resource('faqs', FaqController::class)->except('show');
Route::resource('emergency-contacts', EmergencyContactController::class)->except('show');
Route::resource('knowledge', KnowledgeArticleController::class)->except('show')->parameters(['knowledge' => 'knowledge']);
Route::resource('users', UserController::class)->except('show');
Route::resource('reviews', ReviewController::class)->only(['index', 'destroy']);

Route::get('/unanswered', [UnansweredQuestionController::class, 'index'])->name('unanswered.index');
Route::patch('/unanswered/{question}/resolve', [UnansweredQuestionController::class, 'resolve'])->name('unanswered.resolve');
Route::get('/unanswered/{question}/convert', [UnansweredQuestionController::class, 'convert'])->name('unanswered.convert');

Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
