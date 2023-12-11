<?php

use App\Http\Controllers\auth\AuthController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\VcardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('auth/login', [AuthController::class, 'login']);
Route::middleware('auth:api')->post(
    'auth/logout',
    [AuthController::class, 'logout']
);

// Route::apiResource('/vcard', 'App\Http\Controllers\VcardController');

Route::middleware('auth:api')->group(
    function () {
        Route::get('vcards/{vcard}/exists', [VcardController::class, 'exists']);
        Route::get('vcards/{vcard}/transactions/latest', [TransactionController::class, 'getLatestVCardTransaction']);
        Route::get('vcards/{vcard}/transactions', [TransactionController::class, 'getVCardTransactions']);
        Route::patch('vcards/{vcard}/updatePiggyBankBalance', [VcardController::class, 'updatePiggyBankBalance']);
        Route::delete('vcards/{vcard}/delete', [VcardController::class, 'destroy']);
        Route::apiResource('/vcard', 'App\Http\Controllers\VcardController');

        Route::post('transactions', [TransactionController::class, 'store']);

        Route::get('vcards/contacts', [VcardController::class, 'getVCardContacts']);
    }
);

//Route::apiResource("vcards", VCardController::class);
