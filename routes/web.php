<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [Admin\AuthController::class, 'show'])->name('login');
        Route::post('login', [Admin\AuthController::class, 'login'])->middleware('throttle:10,1');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        // Storefront content
        Route::resource('announcements', Admin\AnnouncementController::class)->except('show');
        Route::resource('slides', Admin\HeroSlideController::class)->except('show');
        Route::post('home-items/headings', [Admin\HomeItemController::class, 'headings'])->name('home-items.headings');
        Route::resource('home-items', Admin\HomeItemController::class)->except('show');
        Route::resource('pages', Admin\PageController::class)->except('show');

        // Catalogue
        Route::resource('products', Admin\ProductController::class)->except('show');
        Route::delete('products/{product}/images/{image}', [Admin\ProductController::class, 'destroyImage'])->name('products.images.destroy');
        Route::resource('categories', Admin\CategoryController::class)->except('show');
        Route::resource('collections', Admin\CollectionController::class)->except('show');

        // Sales
        Route::get('orders/export', [Admin\OrderController::class, 'export'])->name('orders.export');
        Route::resource('orders', Admin\OrderController::class)->only(['index', 'show', 'update']);
        Route::resource('coupons', Admin\CouponController::class)->except('show');
        Route::resource('users', Admin\UserController::class)->except('show');
        Route::get('subscribers', [Admin\SettingController::class, 'subscribers'])->name('subscribers.index');
        Route::get('subscribers/export', [Admin\SettingController::class, 'exportSubscribers'])->name('subscribers.export');
        Route::delete('subscribers/{subscriber}', [Admin\SettingController::class, 'destroySubscriber'])->name('subscribers.destroy');

        // Configuration
        Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
        Route::get('profile', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [Admin\ProfileController::class, 'update'])->name('profile.update');
    });
});
