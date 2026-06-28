<?php

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Filament::auth()->check()) {
        return redirect()->route('filament.admin.pages.dashboard');
    }

    return redirect()->route('filament.admin.auth.login');
});
