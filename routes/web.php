<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Odonto\OdontoCreateController;
use App\Http\Controllers\Odonto\OdontoConsultController;
use App\Http\Controllers\Odonto\OdontoUpdateController;
use App\Http\Controllers\Psicologia\PacienteController;
use App\Http\Controllers\Psicologia\AgendamentoController;
use App\Http\Controllers\Psicologia\ServicoController;


// PÁGINA DE LOGIN - SELEÇÃO DE PSICOLOGIA OU ODONTOLOGIA
Route::get('/', function () {
    if (session()->has('usuario'))
        return view('login');
});

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

Route::get('/odontologia/criarservico', function () {
    return view('odontologia/create_servico');
})->name('criarservico');

Route::get('/odontologia/agendamentos', [OdontoConsultController::class,'getAgendamentos']);

Route::get('/odontologia/criarpaciente', [OdontoCreateController::class, 'showForm'])->name('criarpaciente');
Route::get('/odontologia/criarpaciente/{pacienteId}', [OdontoCreateController::class, 'editPatient'])->name('editPatient');

Route::post('/odontologia/criarpaciente', [OdontoCreateController::class, 'fCreatePatient'])->name('createPatient');
Route::put('/updatePatient/{id}', [OdontoUpdateController::class, 'updatePatient'])->name('updatePatient');

Route::post('/alterarstatus/{agendaId}', [OdontoUpdateController::class,'editStatus'])->name('editStatus');

Route::post('/odontologia/criaragenda', [OdontoCreateController::class, 'fCreateAgenda'])->name('createAgenda');
Route::get('/odontologia/criaragenda/{agendaId}', [OdontoCreateController::class, 'editAgenda'])->name('editAgenda');

Route::get('/getPacientes', [OdontoConsultController::class, 'buscarPacientes']);

Route::get('/getAgenda', [OdontoConsultController::class, 'buscarAgendamentos']);

Route::get('/paciente/{pacienteId}', [OdontoConsultController::class, 'listaPacienteId']);

Route::get('/agenda/{pacienteId}', [OdontoConsultController::class, 'listaAgendamentoId']);

Route::put('/updatePatient/{id}', [OdontoUpdateController::class, 'updatePatient'])->name('updatePatient');

Route::put('/updateAgenda/{id}', [OdontoUpdateController::class, 'updateAgenda'])->name('updateAgenda');

Route::get('/odontologia/consultarpaciente', [OdontoConsultController::class, 'fSelectPatient'])->name('selectPatient');

Route::middleware(['web', 'Auth.Login'])->group(function () {});


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
Route::get('/psicologia/criar-paciente', function () {
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

// CRIAÇÃO DE SERVIÇO
Route::get('/psicologia/criar-servico', function () {
    return view('psicologia/criar_servico');
});

Route::post('/psicologia/criar-servico/criar', [ServicoController::class, 'criarServico'])->name('criarServico-Psicologia');
