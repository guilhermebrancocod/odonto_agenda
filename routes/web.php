<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OdontoController;
use App\Http\Controllers\Psicologia\PacienteController;
use App\Http\Controllers\Psicologia\AgendamentoController;
use App\Http\Controllers\Psicologia\ServicoController;
use App\Http\Controllers\LoginController;
use App\Http\Middleware\AuthMiddleware;


// PÁGINA DE LOGIN - SELEÇÃO DE PSICOLOGIA OU ODONTOLOGIA
Route::get('/', function() {
    if(session()->has('usuario')) {
        return redirect()->route('menu_agenda_psicologia');
    }
    return view('login');
})->name('loginGET');

Route::middleware([AuthMiddleware::class])->group(function() {




    // PÁGINA DE LOGIN
    Route::get('/login', function() {
        if(session()->has('usuario')) {
            return redirect()->route('menu_agenda_psicologia');
        }
        return view('login');
    })->name('loginGET');




    // LOGIN POST
    Route::post('/login', [LoginController::class, 'login'])->name('loginPOST');

    // LOGOUT GET
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');





    // MENU ODONTOLOGIA GET
    Route::get('/odontologia', function() {

        // ARMAZENA DADOS DA SESSÃO

        return view('odontologia/menu_agenda');
    })->name('menu_agenda');

    // MENU PSICOLOGIA GET
    Route::get('/psicologia', function() {

        // ARMAZENA DADOS DA SESSÃO

        return view('psicologia/menu_agenda');
    })->name('menu_agenda');
    





    // ODONTOLOGIA RELATORIO
    Route::get('/odontologia/relatorio', function () {
        return view('odontologia/report_agenda');
    })->name('relatorio');

    // PSICOLOGIA RELATORIO
    Route::get('/psicologia/relatorio', function () {
        return view('psicologia/report_agenda');
    })->name('relatorio');





    // ODONTOLOGIA CRIAR PACIENTE
    Route::get('/odontologia/criarpaciente', function () {
        return view('odontologia/create_patient');
    })->name('criarpaciente');

    // PSICOLOGIA CRIAR PACIENTE
    Route::get('/psicologia/criarpaciente', function () {
        return view('psicologia/create_patient');
    })->name('criarpaciente');
    Route::post('/psicologia/criar-paciente/criar', [PacienteController::class, 'criarPaciente'])->name('criarPaciente-Psicologia');
    





    // ODONTOLOGIA CRIAR AGENDA
    Route::get('/odontologia/criaragenda', function () {
        return view('odontologia/create_agenda');
    })->name('criaragenda');

    // PSICOLOGIA CRIAR AGENDA
    Route::get('/psicologia/criaragenda', function () {
        return view('psicologia/create_agenda');
    })->name('criaragenda');





    // PSICOLOGIA CONSULTA AGENDAMENTO
    Route::get('/psicologia/consultar-agendamento', [AgendamentoController::class, 'getAgendamento'])->name('getAgendamento');




    

    // ODONTOLOGIA CONSULTAR PACIENTE
    Route::get('/odontologia/consultarpaciente', function () {
        return view('odontologia/consult_patient');
    })->name('consultarpaciente');

    // PSICOLOGIA CONSULTAR PACIENTE
    Route::get('/psicologia/consultar-paciente/buscar/', [PacienteController::class, 'getPaciente'])->name('getPaciente');
    Route::get('/psicologia/consultar-paciente/', function () {
        return view('psicologia.consultar_paciente');
    })->name('consultar-paciente');






    // PSICOLOGAI CRIACAO DE SERVIÇO
    Route::get('/psicologia/criar-servico', function() {
        return view('psicologia/criar_servico');
    });
    Route::post('/psicologia/criar-servico/criar', [ServicoController::class, 'criarServico'])->name('criarServico-Psicologia');





    // ODONTOLOGIA INCLUIR PACIENTE
    Route::post('/include/patient', [OdontologiaController::class, 'fIncludePatient'])->name('includePatient');

    // ODONTOLOGIA SELECIONAR PACIENTE
    Route::post('select/patient', [OdontoController::class, 'fSelectPatient'])->name('selectPatient');




});