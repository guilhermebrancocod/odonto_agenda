<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OdontoController;

Route::get('/', function () {
    return view('menu_agenda');
})->name('menu_agenda');

Route::get('/relatorio', function () {
    return view('report_agenda');
})->name('relatorio');

Route::get('/criarpaciente', function () {
    return view('create_patient');
})->name('criarpaciente');

Route::get('/criaragenda', function () {
    return view('create_agenda');
})->name('criaragenda');

Route::get('/consultarpaciente', function () {
    return view('consult_patient');
})->name('consultarpaciente');

Route::middleware(['web', 'Auth.Login'])->group(function () {
    Route::post('include/patient', [OdontoController::class, 'fIncludePatient'])->name('includePatient');
    Route::post('select/patient', [OdontoController::class, 'fSelectPatient'])->name('selectPatient');
});