<?php

use App\Http\Controllers\Api\CompanyTicketTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('companies/{company}/ticket-types', CompanyTicketTypeController::class)
        ->name('api.companies.ticket-types');
});
