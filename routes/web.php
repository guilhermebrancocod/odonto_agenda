<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OdontoController;
use App\Http\Controllers\Psicologia\PacienteController;
use App\Http\Controllers\Psicologia\AgendamentoController;
use App\Http\Controllers\Psicologia\ServicoController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Psicologia\ClinicaController;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\CheckClinicaMiddleware;

// PSICOLOGIA MENU
Route::get('/', function() {
    $usuario = session('usuario');
    session(['last_clinic_route' => 'menu_agenda_psicologia']);
    return view('psicologia/menu_agenda', compact('usuario'));
})->name('menu_agenda_psicologia');

// ODONTOLOGIA MENU
Route::get('/', function() {
    $usuario = session('usuario');
    session(['last_clinic_route' => 'menu_agenda_odontologia']);
    return view('odontologia/menu_agenda', compact('usuario'));
})->name('menu_agenda_odontologia');

Route::get('/', function() {
    if (session()->has('usuario')) {
        $usuario = session('usuario');
        $clinicas = $usuario->pluck('ID_CLINICA')->toArray();

        if (in_array(1, $clinicas) && in_array(2, $clinicas)) {
            // Tem acesso às duas clínicas
            $lastRoute = session('last_clinic_route');

            if ($lastRoute) {
                return redirect()->route($lastRoute);
            } else {
                // Se quiser abrir a tela de seleção
                return redirect()->route('selecionar-clinica-get');
                // ou padrão psicologia:
                // return redirect()->route('menu_agenda_psicologia');
            }
        } elseif (in_array(1, $clinicas)) {
            return redirect()->route('menu_agenda_psicologia');
        } elseif (in_array(2, $clinicas)) {
            return redirect()->route('menu_agenda_odontologia');
        } else {
            session()->flush();
            return redirect()->route('loginGET')->with('error', 'Usuário sem acesso a clínicas.');
        }
    }
    return view('login');
})->name('loginGET');


// 🔹 Rotas de login e seleção de clínica protegidas apenas pelo AuthMiddleware
Route::middleware([AuthMiddleware::class])->group(function() {

    Route::get('/login', function() {
        if (session()->has('usuario')) {
            return redirect()->route('menu_agenda_psicologia');
        }
        return view('login');
    })->name('loginGET');

    Route::post('/login', [LoginController::class, 'login'])->name('loginPOST');

    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/selecionar-clinica', function() {
        return view('selecionar_clinica');
    })->name('selecionar-clinica-get');

    Route::post('/selecionar-clinica', [ClinicaController::class, 'selecionarClinica'])->name('selecionar-clinica-post');
});

// Rotas de PSICOLOGIA protegidas pelo AuthMiddleware + CheckClinicaMiddleware
Route::middleware([AuthMiddleware::class, CheckClinicaMiddleware::class])->prefix('psicologia')->group(function() {

    Route::get('/', function() {
        $usuario = session('usuario');
        return view('psicologia/menu_agenda', compact('usuario'));
    })->name('menu_agenda_psicologia');

    Route::get('/relatorio', function () {
        return view('psicologia/report_agenda');
    })->name('relatorio_psicologia');

    Route::get('/criarpaciente', function () {
        return view('psicologia/create_patient');
    })->name('criarpaciente_psicologia');

    Route::post('/criar-paciente/criar', [PacienteController::class, 'criarPaciente'])->name('criarPaciente-Psicologia');

    Route::get('/criaragenda', function () {
        return view('psicologia/create_agenda');
    })->name('criaragenda_psicologia');

    Route::get('/consultar-agendamento', [AgendamentoController::class, 'getAgendamento'])->name('getAgendamento');

    Route::get('/consultar-paciente/buscar', [PacienteController::class, 'getPaciente'])->name('getPaciente');

    Route::get('/consultar-paciente', function () {
        return view('psicologia.consultar_paciente');
    })->name('consultar-paciente');

    Route::get('/criar-servico', function() {
        return view('psicologia/criar_servico');
    })->name('criarservico_psicologia');

    Route::post('/criar-servico/criar', [ServicoController::class, 'criarServico'])->name('criarServico-Psicologia');

});

// 🔹 Rotas de ODONTOLOGIA protegidas pelo AuthMiddleware + CheckClinicaMiddleware
Route::middleware([AuthMiddleware::class, CheckClinicaMiddleware::class])->prefix('odontologia')->group(function() {

    Route::get('/', function() {
        $usuario = session('usuario');
        return view('odontologia/menu_agenda', compact('usuario'));
    })->name('menu_agenda_odontologia');

    Route::get('/relatorio', function () {
        return view('odontologia/report_agenda');
    })->name('relatorio_odontologia');

    Route::get('/criarpaciente', function () {
        return view('odontologia/create_patient');
    })->name('criarpaciente_odontologia');

    Route::get('/criaragenda', function () {
        return view('odontologia/create_agenda');
    })->name('criaragenda_odontologia');

    Route::get('/consultarpaciente', function () {
        return view('odontologia/consult_patient');
    })->name('consultarpaciente');

    Route::post('/include/patient', [OdontoController::class, 'fIncludePatient'])->name('includePatient');

    Route::post('/select/patient', [OdontoController::class, 'fSelectPatient'])->name('selectPatient');
});
