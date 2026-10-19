<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Rute aplikasi diawali dari PHASE 0 (beranda/layout AdminLTE 3).
| Rute fitur selanjutnya ditambahkan satu per fase sesuai PRD bagian 11.
|
*/

Route::get('/', function () {
    return redirect()->route('home');
});

Route::get('/home', function () {
    return view('home');
})->name('home');
