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
use App\Models\FaesaClinicaServico;
use App\Models\FaesaClinicaPaciente;

use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\CheckClinicaMiddleware;

// PÁGINA DE LOGIN - SELEÇÃO DE PSICOLOGIA OU ODONTOLOGIA
Route::get('/', function () {
    if (session()->has('usuario')) {
        return view('login');
    }

    // $usuario = session('usuario');
    // session(['last_clinic_route' => 'menu_agenda_psicologia']);
    return view('login', compact('usuario'));
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

Route::get('/psicologia', function () {
    $usuario = session('usuario');
    return view('psicologia/menu_agenda', compact('usuario'));
})->name('menu_agenda_psicologia');


Route::get('/relatorios-agendamento', function () {
    return view('psicologia/relatorios_agendamento');
})->name('relatorio_psicologia');

Route::get('/criar-paciente', function () {
    return view('psicologia/criar_paciente');
})->name('criarpaciente_psicologia');

Route::get('/api/buscar-pacientes', function () {
    $query = request()->input('query', '');

    $pacientes = FaesaClinicaPaciente::where(function ($q) use ($query) {
            $q->where('NOME_COMPL_PACIENTE', 'like', "%{$query}%")
              ->orWhere('CPF_PACIENTE', 'like', "%{$query}%");
        })
        ->limit(10)
        ->get(['ID_PACIENTE', 'NOME_COMPL_PACIENTE', 'CPF_PACIENTE']);

    return response()->json($pacientes);
});

Route::post('/criar-paciente/criar', [PacienteController::class, 'criarPaciente'])->name('criarPaciente-Psicologia');

Route::get('/editar-paciente', function () {
    return view('psicologia/editar_paciente');
})->name('editarPaciente-Psicologia');

Route::post('/editar-paciente/{id}', [PacienteController::class, 'editarPaciente'])->name('editarPaciente-Psicologia');

Route::get('/psicologia/criar-agendamento', function () {
    return view('psicologia/criar_agenda');
})->name('criaragenda_psicologia');

Route::post('/psicologia/criar-agendamento/criar', [AgendamentoController::class, 'criarAgendamento'])->name('criarAgendamento-Psicologia');

Route::get('/consultar-agendamento', function () {
    return view('psicologia.consultar_agendamento');
})->name('listagem-agendamentos');
Route::post('/consultar-agendamento/consultar', [AgendamentoController::class, 'getAgendamento'])->name('getAgendamento');


Route::get('/consultar-paciente/buscar', [PacienteController::class, 'getPaciente'])->name('getPaciente');

Route::get('/consultar-paciente', function () {
    return view('psicologia.consultar_paciente');
})->name('consultar-paciente');

Route::get('/criar-servico', function () {
    return view('psicologia/criar_servico');
})->name('criarservico_psicologia');

Route::post('/criar-servico/criar', [ServicoController::class, 'criarServico'])->name('criarServico-Psicologia');

Route::get('/psicologia/pesquisar-servico', [ServicoController::class, 'getServicos'])->name('pesquisarServico-Psicologia');

Route::get('/odontologia/criarservico', function () {
    return view('odontologia/create_servico');
})->name('criarservico');

Route::get('/odontologia/criarbox', function () {
    return view('odontologia/create_box');
})->name('criarbox');

Route::get('/odontologia/agendamentos', [OdontoConsultController::class, 'getAgendamentos']);

Route::get('/odontologia/disciplinas',[OdontoConsultController::class, 'getDisciplinas']);

Route::get('/odontologia/boxes',[OdontoConsultController::class, 'getBoxes']);

Route::get('/odontologia/criarpaciente', [OdontoCreateController::class, 'showForm'])->name('criarpaciente');
Route::get('/odontologia/criarpaciente/{pacienteId}', [OdontoCreateController::class, 'editPatient'])->name('editPatient');

Route::post('/odontologia/criarpaciente', [OdontoCreateController::class, 'fCreatePatient'])->name('createPatient');
Route::put('/updatePatient/{id}', [OdontoUpdateController::class, 'updatePatient'])->name('updatePatient');

Route::post('/odontologia/criarservico', [OdontoCreateController::class, 'createService'])->name('createService');
Route::get('/criarservico/{idService}', [OdontoCreateController::class, 'editService'])->name('editService');
Route::put('/criarservico/{idService}', [OdontoUpdateController::class, 'updateService'])->name('updateService');

Route::post('/odontologia/criarbox', [OdontoCreateController::class, 'createBox'])->name('createBox');
Route::put('/criarbox/{idBox}', [OdontoUpdateController::class, 'updateBox'])->name('updateBox');

Route::post('/odontologia/criarboxdisciplina', [OdontoCreateController::class, 'createBoxDiscipline'])->name('createBoxDiscipline');
Route::put('/criarboxdisciplina/{idBoxDiscipline}', [OdontoUpdateController::class, 'updateBoxDiscipline'])->name('updateBoxDiscipline');

Route::post('/alterarstatus/{agendaId}', [OdontoUpdateController::class, 'editStatus'])->name('editStatus');

Route::post('/odontologia/criaragenda', [OdontoCreateController::class, 'fCreateAgenda'])->name('createAgenda');
Route::get('/odontologia/criaragenda/{agendaId}', [OdontoCreateController::class, 'editAgenda'])->name('editAgenda');

Route::get('/getPacientes', [OdontoConsultController::class, 'buscarPacientes']);

Route::get('/getServices', [OdontoConsultController::class, 'buscarServicos']);

Route::get('/getAgenda', [OdontoConsultController::class, 'buscarAgendamentos']);

Route::get('/getBoxes', [OdontoConsultController::class, 'buscarBoxes']);

Route::get('/getBoxDisciplines', [OdontoConsultController::class, 'buscarBoxeDisciplinas']);

Route::get('/paciente/{pacienteId}', [OdontoConsultController::class, 'listaPacienteId']);

Route::get('/servicos/{servicoId}', [OdontoConsultController::class, 'listaServicosId']);

Route::get('/servicos', [OdontoConsultController::class, 'services']);

Route::get('/agenda/{pacienteId}', [OdontoConsultController::class, 'listaAgendamentoId']);

Route::put('/updatePatient/{id}', [OdontoUpdateController::class, 'updatePatient'])->name('updatePatient');

Route::put('/updateAgenda/{id}', [OdontoUpdateController::class, 'updateAgenda'])->name('updateAgenda');

Route::get('/odontologia/consultarpaciente', [OdontoConsultController::class, 'fSelectPatient'])->name('selectPatient');

Route::get('/odontologia/consultarservico', [OdontoConsultController::class, 'fSelectService'])->name('selectService');

Route::get('/odontologia/consultarbox', [OdontoConsultController::class, 'fSelectBox'])->name('selectBox');

Route::get('/odontologia/consultardisciplinabox', [OdontoConsultController::class, 'fSelectBoxDiscipline'])->name('selectBoxDiscipline');

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

    Route::get('/criarservico', function () {
        return view('odontologia/create_servico');
    })->name('criarservico_odontologia');

    Route::get('/criarbox', function () {
        return view('odontologia/create_box');
    })->name('criarbox_odontologia');

    Route::get('/criarboxdisciplina', function () {
        return view('odontologia/create_box_discipline');
    })->name('criarbox_disciplina_odontologia');
});
// PSICOLOGIA - CRIAÇÃO DE PACIENTE
Route::get('psicologia/criar-paciente', function () {
    return view('psicologia/criar_paciente');
})->name('criar-paciente');

Route::get('/consultarpaciente', function () {
    return view('odontologia/consult_patient');
})->name('consultarpaciente');

Route::get('/consultarservico', function () {
    return view('odontologia/consult_servico');
})->name('consultarservico');

Route::get('/consultarbox', function () {
    return view('odontologia/consult_box');
})->name('consultarbox');

Route::get('/consultardisciplinabox', function () {
    return view('odontologia/consult_box_discipline');
})->name('consultardisciplinabox');

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

Route::get('/api/buscar-servicos', function () {
    $query = request()->input('query', '');
    $servicos = FaesaClinicaServico::where('SERVICO_CLINICA_DESC', 'like', "%{$query}%")
        ->where('ID_CLINICA', 1)
        ->limit(10)
        ->get(['ID_SERVICO_CLINICA', 'SERVICO_CLINICA_DESC']);

    return response()->json($servicos);
});

Route::post('/psicologia/criar-servico/criar', [ServicoController::class, 'criarServico'])->name('criarServico-Psicologia');

Route::get('/psicologia/get-agendamento', [AgendamentoController::class, 'getAgendamento'])->name('getAgendamento');

Route::get('/psicologia/agendamento/{id}', [AgendamentoController::class, 'showAgendamento'])->name('agendamento.show');

// routes/web.php ou routes/api.php
Route::get('/psicologia/agendamentos-calendar', [AgendamentoController::class, 'getAgendamentosForCalendar']);

Route::get('/psicologia/servicos', [ServicoController::class, 'getServicos']);
Route::post('/psicologia/criar-servico', [ServicoController::class, 'criarServico']);
Route::put('/psicologia/servicos/{id}', [ServicoController::class, 'atualizarServico']);
Route::delete('/psicologia/servicos/{id}', [ServicoController::class, 'deletarServico']);

// Para exibir o formulário de edição
Route::get('/psicologia/agendamento/{id}/editar', [AgendamentoController::class, 'editAgendamento'])->name('agendamento.edit');

// Para atualizar
Route::put('/psicologia/agendamento/{id}', [AgendamentoController::class, 'updateAgendamento'])->name('agendamento.update');

Route::delete('/psicologia/agendamento/{id}', [AgendamentoController::class, 'deleteAgendamento'])
     ->name('psicologia.agendamento.delete');
