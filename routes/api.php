<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\UserController; // Asegúrate que existe

// Rutas públicas (sin autenticación)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas (requieren token)
Route::middleware(['is_user_auth'])->group(function () {

    // 🔑 Auth
    Route::controller(AuthController::class)->group(function () {
        Route::post('logout', 'logout');
    });

    // 👤 Perfil
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);

    // 📦 Productos
    Route::get('/products', [ProductController::class, 'getProducts']);
    Route::get('/products/{id}', [ProductController::class, 'getProductById']);

    // 📂 Categorías
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}/products', [ProductController::class, 'getProductsByCategory']);

    // 📊 Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // 🛒 Ventas (usuarios pueden crear y ver sus ventas)
    Route::controller(SalesController::class)->group(function () {
        Route::post('/sales', 'store');      // Crear venta
        Route::get('/sales', 'index');       // Listar ventas
        Route::get('/sales/{id}', 'show');   // Ver detalle
    });

    // ✅ Rutas solo para ADMIN
    Route::middleware(['is_admin'])->group(function () {

        // CRUD productos
        Route::controller(ProductController::class)->group(function () {
            Route::post('/products', 'addProduct');               // Crear producto
            Route::patch('/products/{id}', 'updateProductById');  // Actualizar producto
            Route::delete('/products/{id}', 'deleteProductById'); // Eliminar producto
        });

        // CRUD categorías
        Route::controller(CategoryController::class)->group(function () {
            Route::post('/categories', 'store');               // Crear categoría
            Route::get('/categories/{id}', 'show');            // Ver categoría
            Route::put('/categories/{id}', 'update');          // Actualizar categoría
            Route::delete('/categories/{id}', 'destroy');      // Eliminar categoría
            Route::get('/categories/index', 'getCategories');  // Listar todas las categorías (extra)
        });

        // Reportes
        Route::controller(ReportsController::class)->group(function () {
            Route::get('/reports/summary', 'summary');               // Total ventas y facturado
            Route::get('/reports/sales-by-month', 'salesByMonth');   // Ventas por mes
            Route::get('/reports/top-product', 'topProduct');        // Producto más vendido
        });

        // 👥 Usuarios (listar todos los usuarios)
        Route::get('/users', [UserController::class, 'index']);
    });
});
