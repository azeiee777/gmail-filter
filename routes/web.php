<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GmailController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ping', function () {
    return "Pong ";
});

Route::get('/redirect-to-google', [GmailController::class, 'redirectToGoogle']);
Route::get('/callback', [GmailController::class, 'handleGoogleCallback']);


//975678645934-dh12lugg1vsn46q5q84mav8hm423m6ta.apps.googleusercontent.com