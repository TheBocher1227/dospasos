<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\AuthController;

/**
 * Web Routes
 * 
 * These routes are loaded by the RouteServiceProvider within a group
 * which contains the "web" middleware group. Define routes for web application.
 */

/** 
 * @route GET /login 
 * @name login 
 * @description Displays the login view located in the 'Access' directory.
 */
Route::get('/login', function () {
    return view('Access.login');
})->name('login');

/** 
 * @route GET /register 
 * @name register 
 * @description Displays the register view located in the 'Access' directory.
 */
Route::get('/register', function () {
    return view('Access.register');
})->name('register');

/**
 * Grouped routes under the 'auth' prefix.
 */
Route::group(['prefix' => 'auth'], function () {

    /** 
     * @route POST /auth/post 
     * @name auth.registeruser 
     * @description Handles the user registration via the UsersController.
     */
    Route::post('post', [UsersController::class, 'store'])->name('auth.registeruser');

    /** 
     * @route POST /auth/verify-2fa 
     * @name 2fa.verify 
     * @description Verifies the 2FA code via the AuthController.
     */
    Route::post('verify-2fa', [AuthController::class, 'verify2FACode'])->name('2fa.verify');

    /** 
     * @route GET /auth/verifycode 
     * @name 2fa.view 
     * @description Displays the 2FA verification view located in the 'Access' directory.
     */
    Route::get('/verifycode', function () {
        return view('Access.twofactorcode');
    })->name('2fa.view');

    /** 
     * @route POST /auth/login 
     * @name auth.loginuser 
     * @description Handles the user login via the AuthController.
     */
    Route::post('login', [AuthController::class, 'login'])->name('auth.loginuser');

    /** 
     * @route POST /auth/logout 
     * @name logout 
     * @description Logs out the current user, invalidates the session, and redirects to the login page.
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
     * @description Displays the welcome view, requires 'checkCookie' middleware.
     */
    Route::get('/welcome', function () {
        return view('welcome');
    })->name('welcome')->middleware('checkCookie');

    /** 
     * @route POST /auth/refresh-signed-route 
     * @name refreshsignedroute 
     * @description Refreshes a signed route via the UsersController.
     */
    Route::post('/refresh-signed-route', [UsersController::class, 'refreshSignedRoute'])->name('refreshsignedroute');

    /** 
     * @route GET /auth/activation/{user} 
     * @name access.activation 
     * @description Displays the activation view for a specific user via the UsersController.
     */
    Route::get('/activation/{user}', [UsersController::class, 'showActivationView'])->name('access.activation');
});

/** 
 * @route GET /activate/{user} 
 * @name activate 
 * @description Activates a user account via the AuthController.
 */
Route::get('activate/{user}', [AuthController::class, 'activate'])->name('activate');

/** 
 * @route * 
 * @description Fallback route for undefined URLs, returns a 404 error view.
 */
Route::fallback(function () {
    return response()->view('errors.error404', [], 404);
});
