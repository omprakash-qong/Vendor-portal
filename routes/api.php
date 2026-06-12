<?php

use App\Http\Controllers\Api\V1\MatchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Qong Studio Integration API
|--------------------------------------------------------------------------
|
| All routes here are prefixed with /api automatically.
| Authentication: X-API-Key header (managed in qs_api_keys table)
|
*/

Route::prefix('v1')->middleware('qs.apikey')->group(function () {

    // POST  /api/v1/match       — submit instrument list, get vendor matches
    Route::post('/match', [MatchController::class, 'match'])->name('api.v1.match');

    // GET   /api/v1/match/{id}  — retrieve cached results by QS job ID
    Route::get('/match/{jobId}', [MatchController::class, 'results'])->name('api.v1.match.results');
});
