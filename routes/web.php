<?php
use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

// ── Public
Route::get('/', HomeController::class)->name('home');
Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations.index');
Route::get('/destinations/{destination:slug}', [DestinationController::class, 'show'])->name('destinations.show');
Route::get('/map', [MapController::class, 'index'])->name('map');
Route::get('/accommodations', [AccommodationController::class, 'index'])->name('accommodations.index');
Route::get('/accommodations/{accommodation:slug}', [AccommodationController::class, 'show'])->name('accommodations.show');
Route::get('/restaurants', [RestaurantController::class, 'index'])->name('restaurants.index');
Route::get('/restaurants/{restaurant:slug}', [RestaurantController::class, 'show'])->name('restaurants.show');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery');
Route::get('/faqs', [FaqController::class, 'index'])->name('faqs');
Route::view('/about', 'public.about')->name('about');

// ── Guest auth
Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class, 'show'])->name('login');
    Route::post('/login',   [LoginController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register',[RegisterController::class, 'store'])->middleware('throttle:6,1');
});

// ── Authenticated
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/destinations/{destination:slug}/reviews', [DestinationController::class, 'storeReview'])
        ->name('reviews.store');

    Route::post('/destinations/{destination:slug}/favorite', [FavoriteController::class, 'toggle'])
        ->name('favorites.toggle');
});

// ── Admin
Route::middleware(['auth', 'role:admin', 'admin.log'])
    ->prefix('admin')->name('admin.')
    ->group(base_path('routes/admin.php'));
