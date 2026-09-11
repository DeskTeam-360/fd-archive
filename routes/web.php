<?php

use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/import', 'pages.freshdesk-import')->name('freshdesk-import');

    Route::view('/archive', 'pages.archive')->name('archive');
    Route::view('/archive/companies/{id}', 'pages.archive-company')->name('archive.company');
    Route::view('/archive/contacts/{id}', 'pages.archive-contact')->name('archive.contact');
    Route::view('/archive/tickets/{id}', 'pages.archive-ticket')->name('archive.ticket');
});

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::view('dashboard', 'dashboard')->name('dashboard');
    });

require __DIR__.'/settings.php';
