<?php
use App\Http\Controllers\AdminUsers\AdminUserController;
use App\Http\Controllers\AdminUsers\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Status\StatusController;
use App\Http\Controllers\User\UserController;

//Syntax: <Project URL>/api/register

//Auth Open Routes
Route::post('api/phenix-hr/register', [AuthController::class, 'register']);
Route::post('api/phenix-hr/guest-register', [AuthController::class, 'guestRegister']);
Route::post('api/phenix-hr/guest-verify', [AuthController::class, 'VerifyGuestUser']);
Route::post('api/phenix-hr/login', [AuthController::class, 'login'])->name('template.login');

Route::group([
    "middleware" => ['auth:api', 'api_verified']
], function () {
    Route::get('api/phenix-hr/profile', [AuthController::class, 'profile']);
    Route::get('api/phenix-hr/logout', [AuthController::class, 'logout']);

    //Users
    Route::resource('api/phenix-hr/users', UserController::class, ['only' => ['show']]);
});


Route::group(['prefix' => 'api/phenix-hr'], function () {
    Route::post('/forgot-password', [ForgotPasswordController::class, 'getResetToken']);
    Route::get('/password-reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.reset.form');

    Route::resource('/users', UserController::class, ['except' => ['create', 'edit', 'show']]);

    //Admin Users
    Route::post('/admin-register', [AdminController::class, 'register']);
    Route::post('/admin-login', [AdminController::class, 'login'])->name('template.admin.login');
    Route::get('/admin-logout', [AdminController::class, 'logout']);
    Route::resource('/admin-users', AdminUserController::class, ['except' => ['create', 'edit', 'show']]);

});