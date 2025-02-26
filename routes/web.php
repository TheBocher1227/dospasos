<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\AuthController;

/**
 * Web Routes
 * 
 * Estas rutas son cargadas por RouteServiceProvider dentro de un grupo
 * que contiene el middleware 'web'. Define rutas para la aplicación.
 */

/** 
 * @route GET / 
 * @name home 
 * @description Muestra la vista de bienvenida.
 */
Route::get('/', function () {
    return view('welcome');
})->name('home')->middleware('checkCookie');

/**
 * Rutas con límite de peticiones para login, register y verifycode
 * 
 * - Login: 3 intentos por minuto.
 * - Register: 5 intentos por minuto.
 * - Verify Code: 7 intentos por minuto.
 */
Route::middleware('throttle:20,1')->group(function () {
    Route::get('/login', function () {
        return view('access.login');
    })->name('login');

    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.loginuser');
});

Route::middleware('throttle:20,1')->group(function () {
    Route::get('/register', function () {
        return view('access.register');
    })->name('register');

    Route::post('/auth/post', [UsersController::class, 'store'])->name('auth.registeruser');
});

Route::middleware('throttle:20,1')->group(function () {
    Route::get('/auth/verifycode', function () {
        return view('access.twofactorcode');
    })->name('2fa.view');

    Route::post('/auth/verify-2fa', [AuthController::class, 'verify2FACode'])->name('2fa.verify');
});

/**
 * Grupo de rutas bajo el prefijo 'auth'.
 */
Route::group(['prefix' => 'auth'], function () {

    /** 
     * @route POST /auth/logout 
     * @name logout 
     * @description Cierra sesión y redirige a login.
     */
    Route::post('/logout', function () {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');

    /** 
     * @route GET /auth/welcome 
     * @name welcome 
     * @middleware checkCookie 
     * @description Muestra la vista de bienvenida con middleware de cookie.
     */
    Route::get('/welcome', function () {
        return view('welcome');
    })->name('welcome')->middleware('checkCookie');

    /** 
     * @route POST /auth/refresh-signed-route 
     * @name refreshsignedroute 
     * @description Refresca la ruta firmada a través del UsersController.
     */
    Route::post('/refresh-signed-route', [UsersController::class, 'refreshSignedRoute'])->name('refreshsignedroute');

    /** 
     * @route GET /auth/activation/{user} 
     * @name access.activation 
     * @description Muestra la vista de activación de usuario.
     */
    Route::get('/activation/{user}', [UsersController::class, 'showActivationView'])->name('access.activation');
});

/** 
 * @route GET /activate/{user} 
 * @name activate 
 * @description Activa la cuenta de usuario.
 */
Route::get('activate/{user}', [AuthController::class, 'activate'])->name('activate');

/** 
 * @route * 
 * @description Ruta fallback para URLs no definidas, devuelve error 404.
 */
Route::fallback(function () {
    return response()->view('errors.error404', [], 404);
});
