<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/chamados');

Route::resource('chamados', TicketController::class)
    ->parameters(['chamados' => 'ticket']);
