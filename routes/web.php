<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OdontoController;
use App\Http\Controllers\Psicologia\PacienteController;
use App\Http\Controllers\Psicologia\AgendamentoController;

// ODONTOLOGIA
Route::get('/', function () {
    return view('odontologia/menu_agenda');
})->name('menu_agenda');

Route::get('/odontologia/menu', function () {
    return view('odontologia/menu_agenda');
})->name('menu_agenda');

Route::get('/odontologia', function () {
    return view('odontologia/menu_agenda');
})->name('menu_agenda');

Route::get('/odontologia/relatorio', function () {
    return view('odontologia/report_agenda');
})->name('relatorio');

Route::get('/odontologia/criarpaciente', function () {
    return view('odontologia/create_patient');
})->name('criarpaciente');

Route::get('/odontologia/criaragenda', function () {
    return view('odontologia/create_agenda');
})->name('criaragenda');

Route::get('/odontologia/consultarpaciente', function () {
    return view('odontologia/consult_patient');
})->name('consultarpaciente');

Route::get('/odontologia/consultaragenda', function () {
    return view('odontologia/consult_agenda');
})->name('consultaragenda');

Route::middleware(['web', 'Auth.Login'])->group(function () {
    Route::post('include/patient', [OdontoController::class, 'fIncludePatient'])->name('includePatient');
    Route::post('select/patient', [OdontoController::class, 'fSelectPatient'])->name('selectPatient');
});


// PSICOLOGIA
Route::get('/psicologia/menu', function () {
    return view('psicologia/menu_agenda');
})->name('menu_agenda');

Route::get('/psicologia', function () {
    return view('psicologia/menu_agenda');
})->name('menu_agenda');

Route::get('/psicologia/relatorio', function () {
    return view('psicologia/report_agenda');
})->name('relatorio');


// PSICOLOGIA - CRIAÇÃO DE PACIENTE
Route::get('/psicologia/criar-paciente', function() {
    return view('/psicologia/criar_paciente');
})->name('criar-paciente');

Route::post('/psicologia/criar-paciente/criar', [PacienteController::class, 'criarPaciente'])->name('criarPaciente-Psicologia');

// CRIAÇÃO DE AGENDA
Route::get('/psicologia/criar-agenda', function () {
    return view('psicologia/criar_agenda');
})->name('criar-agenda');

// PÁGINA DE CONSULTA DE PACIENTE
Route::get('/psicologia/consultar-paciente/', function () {
    return view('psicologia.consultar_paciente');
})->name('consultar-paciente');

// CONSULTA DE PACIENTE
Route::get('/psicologia/consultar-paciente/buscar/', [PacienteController::class, 'getPaciente'])->name('getPaciente');

// CONSULTA DE AGENDAMENTO
Route::get('/psicologia/consultar-agendamento', [AgendamentoController::class, 'getAgendamento'])->name('getAgendamento');