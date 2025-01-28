<?php

use App\Http\Controllers\AuthManager;
use App\Http\Controllers\ProductsManager;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [ProductsManager::class, 'index'])->name('home');


Route::get("login", [AuthManager::class , "login"])
    ->name('login');

Route::get("logout", [AuthManager::class , "logout"])
    ->name('logout');

Route::post("login", [AuthManager::class , "loginPost"])
    ->name('login.post');

Route::get("register", [AuthManager::class , "register"])
    ->name('register');

Route::post("register", [AuthManager::class , "registerPost"])
    ->name('register.post');

Route::get('/product/{slug}', [ProductsManager::class, 'details'])
    ->name('products.details');

Route::middleware("auth")->group(function () {
    Route::get('/cart/{id}', [ProductsManager::class, 'addToCart'])
        ->name('cart.add');
});

Route::get('/cart', [ProductsManager::class, 'showCart'])
    ->name('cart.show');


