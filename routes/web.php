<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Odonto\OdontoCreateController;
use App\Http\Controllers\Odonto\OdontoConsultController;
use App\Http\Controllers\Odonto\OdontoUpdateController;
use App\Http\Controllers\Psicologia\PacienteController;
use App\Http\Controllers\Psicologia\AgendamentoController;
use App\Http\Controllers\Psicologia\ServicoController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Psicologia\ClinicaController;

use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\CheckClinicaMiddleware;

// PÁGINA DE LOGIN - SELEÇÃO DE PSICOLOGIA OU ODONTOLOGIA
Route::get('/', function () {
    if (session()->has('usuario')) {
        return view('login');
    }

    $usuario = session('usuario');
    session(['last_clinic_route' => 'menu_agenda_psicologia']);
    return view('psicologia.menu_agenda', compact('usuario'));
})->name('menu_agenda_psicologia');

// ODONTOLOGIA MENU
Route::get('/', function () {
    $usuario = session('usuario');
    session(['last_clinic_route' => 'menu_agenda_odontologia']);
    return view('odontologia/menu_agenda', compact('usuario'));
})->name('menu_agenda_odontologia');

Route::get('/', function () {
    if (session()->has('usuario')) {
        $usuario = session('usuario');
        $clinicas = $usuario->pluck('ID_CLINICA')->toArray();
        $sit_usuario = session('SIT_USUARIO');

        if (in_array(1, $clinicas) && in_array(2, $clinicas)) {
            // SESSÃO AINDA EXISTE - TEM ACESSO ÀS DUAS CLÍNICAS
            $lastRoute = session('last_clinic_route');

            if ($lastRoute) {
                return redirect()->route($lastRoute);
            } else {
                // ABRE TELA DE SELEÇÃO - Se não tem LastRoute gravado, abre tela para seleção de clínica que deseja acessar
                return redirect()->route('selecionar-clinica-get');
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

Route::middleware([AuthMiddleware::class])->group(function () {

    Route::get('/login', function () {
        if (session()->has('usuario')) {
            return redirect()->route('menu_agenda_psicologia');
        }
        return view('login');
    })->name('loginGET');

    Route::post('/login', [LoginController::class, 'login'])->name('loginPOST');

    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/selecionar-clinica', function () {
        return view('selecionar_clinica');
    })->name('selecionar-clinica-get');

    Route::post('/selecionar-clinica', [ClinicaController::class, 'selecionarClinica'])->name('selecionar-clinica-post');
});

Route::middleware([AuthMiddleware::class, CheckClinicaMiddleware::class])->prefix('psicologia')->group(function () {}); /*ADICIONAR QUANDO TUDO ESTIVER RODANDO*/

Route::get('/', function () {
    $usuario = session('usuario');
    return view('psicologia/menu_agenda', compact('usuario'));
})->name('menu_agenda_psicologia');


Route::get('/relatorios-agendamento', function () {
    return view('psicologia/relatorios_agendamento');
})->name('relatorio_psicologia');

Route::get('/criar-paciente', function () {
    return view('psicologia/criar_paciente');
})->name('criarpaciente_psicologia');


Route::post('/criar-paciente/criar', [PacienteController::class, 'criarPaciente'])->name('criarPaciente-Psicologia');

Route::get('/editar-paciente', function () {
    return view('psicologia/editar_paciente');
})->name('editarPaciente-Psicologia');

Route::post('/editar-paciente/{id}', [PacienteController::class, 'editarPaciente'])->name('editarPaciente-Psicologia');

Route::get('/criar-agendamento', function () {
    return view('psicologia/criar_agenda');
})->name('criaragenda_psicologia');

Route::post('/criar-agendamento/criar', [AgendamentoController::class, 'criarAgendamento'])->name('criarAgendamento-Psicologia');

Route::get('/consultar-agendamento', function () {
    return view('psicologia.consultar_agendamento');
});
Route::post('/consultar-agendamento/consultar', [AgendamentoController::class, 'getAgendamento'])->name('getAgendamento');


Route::get('/consultar-paciente/buscar', [PacienteController::class, 'getPaciente'])->name('getPaciente');

Route::get('/consultar-paciente', function () {
    return view('psicologia.consultar_paciente');
})->name('consultar-paciente');

Route::get('/criar-servico', function () {
    return view('psicologia/criar_servico');
})->name('criarservico_psicologia');

Route::post('/criar-servico/criar', [ServicoController::class, 'criarServico'])->name('criarServico-Psicologia');

Route::get('/pesquisar-servico', [ServicoController::class, 'getServico'])->name('pesquisarServico-Psicologia');


Route::get('/odontologia/criarservico', function () {
    return view('odontologia/create_servico');
})->name('criarservico');

Route::get('/odontologia/agendamentos', [OdontoConsultController::class, 'getAgendamentos']);

Route::get('/odontologia/criarpaciente', [OdontoCreateController::class, 'showForm'])->name('criarpaciente');
Route::get('/odontologia/criarpaciente/{pacienteId}', [OdontoCreateController::class, 'editPatient'])->name('editPatient');

Route::post('/odontologia/criarpaciente', [OdontoCreateController::class, 'fCreatePatient'])->name('createPatient');
Route::put('/updatePatient/{id}', [OdontoUpdateController::class, 'updatePatient'])->name('updatePatient');

Route::post('/alterarstatus/{agendaId}', [OdontoUpdateController::class, 'editStatus'])->name('editStatus');

Route::post('/odontologia/criaragenda', [OdontoCreateController::class, 'fCreateAgenda'])->name('createAgenda');
Route::get('/odontologia/criaragenda/{agendaId}', [OdontoCreateController::class, 'editAgenda'])->name('editAgenda');

Route::get('/getPacientes', [OdontoConsultController::class, 'buscarPacientes']);

Route::get('/getAgenda', [OdontoConsultController::class, 'buscarAgendamentos']);

Route::get('/paciente/{pacienteId}', [OdontoConsultController::class, 'listaPacienteId']);

Route::get('/agenda/{pacienteId}', [OdontoConsultController::class, 'listaAgendamentoId']);

Route::put('/updatePatient/{id}', [OdontoUpdateController::class, 'updatePatient'])->name('updatePatient');

Route::put('/updateAgenda/{id}', [OdontoUpdateController::class, 'updateAgenda'])->name('updateAgenda');

Route::get('/odontologia/consultarpaciente', [OdontoConsultController::class, 'fSelectPatient'])->name('selectPatient');

Route::get('/odontologia/consultaragenda', [OdontoConsultController::class, 'fSelectAgenda'])->name('selectAgenda');

Route::middleware(['web', 'Auth.Login'])->group(function () {});

Route::get('/odontologia/menu_agenda_odontologia', function () {
    $usuario = session('usuario');
    return view('odontologia/menu_agenda_odontologia', compact('usuario'));
})->name('menu_agenda_odontologia');

Route::middleware([AuthMiddleware::class, CheckClinicaMiddleware::class])->prefix('odontologia')->group(function () {

    Route::get('/', function () {
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
});
// PSICOLOGIA - CRIAÇÃO DE PACIENTE
Route::get('psicologia/criar-paciente', function () {
    return view('psicologia/criar_paciente');
})->name('criar-paciente');

Route::get('/consultarpaciente', function () {
    return view('odontologia/consult_patient');
})->name('consultarpaciente');


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
Route::get('/psicologia/consultar-agendamento', function () {
    return view('psicologia.consultar_agendamento');
});

// CRIAÇÃO DE SERVIÇO
Route::get('psicologia/criar-servico', function () {
    return view('psicologia/criar_servico');
});

Route::post('/psicologia/criar-servico/criar', [ServicoController::class, 'criarServico'])->name('criarServico-Psicologia');

Route::get('/psicologia/get-agendamento', [AgendamentoController::class, 'getAgendamento'])->name('getAgendamento');

Route::get('/psicologia/agendamento/{id}', [AgendamentoController::class, 'show'])->name('agendamento.show');

// routes/web.php ou routes/api.php
Route::get('/psicologia/agendamentos-calendar', [AgendamentoController::class, 'getAgendamentosForCalendar']);

Route::get('/psicologia/servicos', [ServicoController::class, 'getServicos']);
Route::put('/psicologia/servicos/{id}', [ServicoController::class, 'atualizarServico']);
