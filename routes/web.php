<?php

use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/import', 'pages.freshdesk-import')->name('freshdesk-import');

    Route::redirect('/archive', '/archive/tickets')->name('archive');
    Route::get('/archive/tickets', [\App\Http\Controllers\Archive\TicketsController::class, 'index'])->name('archive.tickets');
    Route::livewire('/archive/contacts', \App\Livewire\Archive\ArchiveContacts::class)->name('archive.contacts');
    Route::livewire('/archive/companies', \App\Livewire\Archive\ArchiveCompanies::class)->name('archive.companies');
    Route::view('/archive/companies/{id}', 'pages.archive-company')->name('archive.company');
    Route::view('/archive/contacts/{id}', 'pages.archive-contact')->name('archive.contact');
    Route::view('/archive/tickets/{id}', 'pages.archive-ticket')->name('archive.ticket');

    Route::livewire('/users', \App\Livewire\Users::class)->name('users.index');
});

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::view('dashboard', 'dashboard')->name('dashboard');
    });

require __DIR__.'/settings.php';
