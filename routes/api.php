<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserRequestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::name('api.')->prefix('admin')->group(function () {
    // Authentication Routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    
    
    Route::group(['middleware' => 'admin'], function () {
        Route::post('/create', [AuthController::class, 'createNewAdmin']);
        Route::get('/admins', [AuthController::class, 'getAllAdminUsers']);
        Route::get('/admin/{id}', [AuthController::class, 'getAdminUser']);
        Route::put('/updateRole', [AuthController::class, 'updateAdminRole']);

        Route::post('/mapping/create', [App\Http\Controllers\AdminMappingController::class, 'mapAdmin']);
        Route::post('/mapping/approve', [App\Http\Controllers\AdminMappingController::class, 'approveAdminMapping']);
        Route::get('/mapping/getAllMapping', [App\Http\Controllers\AdminMappingController::class, 'getAllMappings']);
        Route::get('/mapping/getAllMappingByStatus', [App\Http\Controllers\AdminMappingController::class, 'getAllMappingsByStatus']);
        Route::get('/mapping/getMappingById', [App\Http\Controllers\AdminMappingController::class, 'getMappingById']);
        Route::get('/mapping/getMappingByIdStatus', [App\Http\Controllers\AdminMappingController::class, 'getMappingByIdStatus']);
        Route::get('/mapping/{id}', [App\Http\Controllers\AdminMappingController::class, 'index']);
        

        Route::post('/request/create', [UserRequestController::class, 'create']);
        Route::post('/request/approve', [UserRequestController::class, 'approve']);
        Route::post('/request/decline', [UserRequestController::class, 'decline']);
        Route::get('/requests', [UserRequestController::class, 'index']);
        Route::get('/request/{id}', [UserRequestController::class, 'getRequestbyID']);
        Route::get('/requests/getRequestbyStatus', [UserRequestController::class, 'getRequestbyStatus']);
        Route::get('/requests/getRequestbyInitiatorId', [UserRequestController::class, 'getRequestbyInitiatorId']);
        Route::get('/requests/getRequestbyInitiatorIdStatus', [UserRequestController::class, 'getRequestbyInitiatorIdStatus']);
        Route::get('/requests/getRequestbyAuthorizerId', [UserRequestController::class, 'getRequestbyAuthorizerId']);
        Route::get('/requests/getRequestbyAuthorizerIdStatus', [UserRequestController::class, 'getRequestbyAuthorizerIdStatus']);

        Route::get('/usersProfile', [App\Http\Controllers\UserProfileController::class, 'index']);
    });

});

Route::any('{any}', function(){
    return response()->json([
        'isSuccessful'    => false,
        'responseMessage'   => 'Page Not Found.',
    ], 404);
})->where('any', '.*');