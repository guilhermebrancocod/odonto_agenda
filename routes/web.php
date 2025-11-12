<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

use App\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Odonto\PatientController;
use App\Http\Controllers\Odonto\AgendaController;
use App\Http\Controllers\Odonto\UserController;
use App\Http\Controllers\Odonto\ServiceController;
use App\Http\Controllers\Odonto\BoxDisciplineStudentsController;
use App\Http\Controllers\Odonto\BoxesController;
use App\Http\Controllers\Odonto\EncaminhamentoController;
use App\Http\Controllers\Odonto\ReportController;
use App\Http\Controllers\Odonto\CalendarioController;

//-----RELATORIOS---//
use App\Http\Controllers\Odonto\Relatorios\RelatorioAgendamentoController;
use App\Http\Controllers\Odonto\Relatorios\RelatorioEncaminhamentoController;
use App\Http\Controllers\Odonto\Relatorios\RelatorioFinanceiroController;
use App\Http\Controllers\Odonto\Relatorios\RelatorioDisciplinaBoxController;
use App\Http\Controllers\Odonto\Relatorios\RelatorioAcessoController;
use App\Http\Controllers\Odonto\Relatorios\RelatorioUsuarioController;

// -------------------- ODONTOLOGIA --------------------

// ===== LOGIN (SEM middleware!) =====
Route::get('/login', function () {
    if (session()->has('usuario') || Auth::check()) {
        return redirect()->route('menu_agenda');
    }
    return view('login');
})->name('loginGET');

Route::post('/login', [LoginController::class, 'login'])->name('loginPOST');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ===== RAIZ "/" ÚNICA =====
// Se não logado -> /login; se logado -> menu.
Route::get('/', function () {
    if (!session()->has('usuario') && !Auth::check()) {
        return redirect()->route('loginGET');
    }
    return redirect()->route('menu_agenda');
});

// MIDDLEWARE DE ROTAS ODONTOLOGIA

// Menu acessível a Admin, Coordenador e Usuario
Route::middleware(['web', AuthMiddleware::class . ':Admin,Coordenador,Usuario'])
    ->get('/odontologia/menu_agenda', function () {
        $usuario = session('usuario');
        return view('odontologia/menu_agenda', compact('usuario'));
    })->name('menu_agenda');

// ---------- Views administrativas (somente Admin/Coordenador) ----------
Route::middleware([AuthMiddleware::class . ':Admin,Coordenador'])->group(function () {

    Route::get('/usuarios', function () {
        return view('odontologia/usuarios');
    })->name('usuarios_odontologia');

    Route::get('/relatorio', function () {
        return view('odontologia/report_agenda');
    })->name('relatorio_odontologia');

    Route::get('relatorio/agendamentos', function () {
        return view('odontologia/relatorio/agendamentos');
    })->name('relatorio.agendamentos');

    Route::get('relatorio/encaminhamentos', function () {
        return view('odontologia/relatorio/encaminhamentos');
    })->name('relatorio.encaminhamento');

    Route::get('relatorio/financeiro', function () {
        return view('odontologia/relatorio/financeiro');
    })->name('relatorio.financeiro');

    Route::get('relatorio/acessos', function () {
        return view('odontologia/relatorio/acessos');
    })->name('relatorio.acessos');

    Route::get('/odontologia/criarpaciente', function () {
        return view('odontologia/create_patient');
    })->name('criarpaciente');

    Route::get('/odontologia/criaragenda', function () {
        return view('odontologia/create_agenda');
    })->name('criaragenda');

    Route::get('/odontologia/criarboxdisciplina', function () {
        return view('odontologia/create_box_discipline');
    })->name('criarboxdisciplina');

    Route::get('criarservico', function () {
        return view('odontologia/create_service');
    })->name('criarservico_odontologia');

    Route::get('/odontologia/criarusuario', function () {
        return view('odontologia/create_user');
    })->name('criarusuario_odontologia');

    Route::get('/criarboxdisciplina', function () {
        return view('odontologia/create_box_discipline');
    })->name('criarbox_disciplina_odontologia');
});

//USER
Route::post('/odontologia/criarusuario', [UserController::class, 'createUsuario'])->name('createUsuario');
Route::get('/odontologia/criarusuario/{userId}', [UserController::class, 'editUser'])->name('editUser');
Route::put('/updateUser/{userId}', [UserController::class, 'updateUser'])->name('updateUser');

//LOG
Route::get('/odontologia/pacientes/{id}/audits', [UserController::class, 'historyPaciente'])->name('pacientes.audit');

// CRIAÇÃO E EDIÇÃO - PACIENTE
Route::get('/odontologia/criarpaciente', [PatientController::class, 'showForm'])->name('criarpaciente');
Route::get('/odontologia/criarpaciente/{pacienteId}', [PatientController::class, 'editarPaciente'])->name('editarPaciente');
Route::post('/odontologia/criarpaciente', [PatientController::class, 'fCreatePatient'])->name('createPatient');
Route::put('/updatePatient/{id}', [PatientController::class, 'updatePatient'])->name('updatePatient');
Route::put('/updateUser/{id}', [UserController::class, 'updateUser'])->name('updateUser');

// SERVIÇOS
Route::get('/odontologia/criarservico', function () {
    return view('odontologia/create_service');
})->name('criarservico');
Route::post('/odontologia/criarservico', [ServiceController::class, 'createProcedures'])->name('createProcedures');
Route::get('/criarservico/{idService}', [ServiceController::class, 'editService'])->name('editService');
Route::put('/criarservico/{idService}', [ServiceController::class, 'updateProcedures'])->name('updateProcedures');

// BOXES
Route::get('/odontologia/criarbox', function () {
    return view('odontologia/create_box');
})->name('criarbox');
Route::post('/odontologia/criarbox', [BoxesController::class, 'createBox'])->name('createBox');
Route::get('/odontologia/criarbox/{boxId}', [BoxesController::class, 'editBox'])->name('editBox');
Route::put('/criarbox/{boxId}', [BoxesController::class, 'updateBox'])->name('updateBox');

Route::get('/odontologia/trocabox', [BoxesController::class, 'replaceBoxDiscipline'])->name('box.troca');
Route::post('/odontologia/trocabox/{id}', [BoxesController::class, 'updateReplaceBox'])->name('box.troca.update');

// BOX-DISCIPLINAS
Route::post('/odontologia/criarboxdisciplina', [BoxDisciplineStudentsController::class, 'createBoxDiscipline'])->name('createBoxDiscipline');
Route::get('/odontologia/criarboxdisciplina/{idBoxDiscipline}', [BoxDisciplineStudentsController::class, 'editBoxDiscipline'])->name('editBoxDiscipline');
Route::get('/odontologia/deleteboxdisciplina/{idBoxDiscipline}', [BoxDisciplineStudentsController::class, 'deleteBoxDiscipline'])->name('deleteBoxDiscipline');
Route::put('/criarboxdisciplina/{idBoxDiscipline}', [BoxDisciplineStudentsController::class, 'updateBoxDiscipline'])->name('updateBoxDiscipline');

// AGENDA
Route::post('/odontologia/criaragenda', [AgendaController::class, 'createAgenda'])->name('createAgenda');
Route::get('/odontologia/criaragenda/{agendaId}', [AgendaController::class, 'editAgenda'])->name('editAgenda');
Route::put('/updateAgenda/{id}', [AgendaController::class, 'updateAgenda'])->name('updateAgenda');

//PERFIL
Route::get('/perfil', [LoginController::class, 'login']);

Route::middleware([AuthMiddleware::class . ':Admin,Coordenador,Usuario'])->group(function () {
    //CALENDÁRIO
    Route::get('/odontologia/agendamentos', [CalendarioController::class, 'getAgendamentos']);
    Route::get('/odontologia/agendamentos/alunos-sem-agendamento', [CalendarioController::class, 'getAlunosSemAgendamento']);
});

// CONSULTAS (controllers que geram views) — restritas a Usuario
Route::middleware([AuthMiddleware::class . ':Admin,Coordenador'])->group(function () {
    Route::get('/odontologia/disciplinascombox/{diasemana}', [BoxDisciplineStudentsController::class, 'disciplinascombox']);
    Route::get('/getBoxDisciplines/{discipline}/{diasemana}', [BoxDisciplineStudentsController::class, 'boxesDisciplina']);
    Route::get('/getBoxDisciplines/{idboxdisciplina}', [BoxDisciplineStudentsController::class, 'boxesDisciplinaId']);
    Route::get('/procedimentos', [ServiceController::class, 'procedimento']);
    Route::get('/odontologia/turma/{diasemana}/', [BoxDisciplineStudentsController::class, 'getTodasTurmas']);
    Route::get('/odontologia/turmasAgendadas/', [BoxDisciplineStudentsController::class, 'getTurmasAgendadas']);
    Route::get('/odontologia/turmasAgendadas/{turmaSelecionada}', [BoxDisciplineStudentsController::class, 'getTodasTurmasSelecionada']);
    Route::get('/getHorariosBoxDisciplinas/{discipline}', [BoxDisciplineStudentsController::class, 'getHorariosBoxDisciplinas']);
    Route::get('/odontologia/horarios/{disciplina}/{turma}/{diasemana}', [AgendaController::class, 'getHorariosDatasTurmaDisciplina']);
    Route::get('/odontologia/disciplinas/', [BoxDisciplineStudentsController::class, 'getDisciplinas']);
    Route::get('/odontologia/turmas/{diasemana}', [BoxDisciplineStudentsController::class, 'getTurmas']);
    Route::get('/odontologia/datas/{disciplina}/{turma}', [AgendaController::class, 'getDatasTurmaDisciplina']);
    Route::get('/odontologia/alunos/{disciplina}/{turma}', [AgendaController::class, 'getAlunosDisciplinaTurma']);
    Route::get('/odontologia/alunos/{disciplina}/{turma}/{box}', [AgendaController::class, 'getAlunosDisciplinaTurmaAgenda']);
    Route::get('/odontologia/boxes', [BoxesController::class, 'getBoxes']);
    Route::get('/odontologia/boxes/{boxId}', [BoxesController::class, 'getBoxeId']);
    Route::get('/odontologia/user/{userId}', [UserController::class, 'getUserId']);

    Route::get('/getPacientes', [PatientController::class, 'buscarPacientes']);
    Route::get('/getProcedures', [ServiceController::class, 'buscarProcedimentos']);
    Route::get('/getBoxes', [BoxesController::class, 'buscarBoxes']);
    Route::get('/getUser', [UserController::class, 'buscarUsuarios']);
    Route::get('/getUserLyceum', [UserController::class, 'buscarUsuariosLyceum']);
    Route::get('/getUserLyceum/{pessoa}', [UserController::class, 'buscarPessoaLyceum']);
    Route::get('/getBoxDisciplines', [BoxDisciplineStudentsController::class, 'buscarBoxeDisciplinas']);
    Route::get('/consultaboxdisciplina/{idBoxDiscipline}', [BoxDisciplineStudentsController::class, 'consultaboxdisciplina']);
    Route::get('/paciente/{pacienteId}', [PatientController::class, 'listaPacienteId']);
    Route::get('/servicos/{servicoId}', [BoxDisciplineStudentsController::class, 'listaServicosId']);
    Route::get('/agenda/{pacienteId}', [AgendaController::class, 'listaAgendamentoId']);
});

// CONSULTA DE VIEWS — restritas a Usuario
Route::middleware([AuthMiddleware::class . ':Admin,Coordenador'])->group(function () {
    Route::get('/odontologia/consultarpaciente', [PatientController::class, 'fSelectPatient'])->name('selectPatient');
    Route::get('/odontologia/consultarservico', [ServiceController::class, 'fSelectService'])->name('selectService');
    Route::get('/odontologia/consultarbox', [BoxesController::class, 'fSelectBox'])->name('selectBox');
    Route::get('/odontologia/consultarusuario', [UserController::class, 'selectUser'])->name('selectUser');
    Route::get('/odontologia/consultardisciplinabox', [BoxDisciplineStudentsController::class, 'fSelectBoxDiscipline'])->name('selectBoxDiscipline');
    Route::get('/odontologia/consultaragenda', [AgendaController::class, 'fSelectAgenda'])->name('selectAgenda');
});

Route::middleware([AuthMiddleware::class . ':Admin,Coordenador,Usuario'])->group(function () {
Route::get('/odontologia/consultaragenda', [AgendaController::class, 'fSelectAgenda'])->name('selectAgenda');
Route::get('/getAgenda', [AgendaController::class, 'buscarAgendamentos']);
});

//ENCAMINHAMENTO (sem mudança)
Route::prefix('odontologia/encaminhamentos')->group(function () {
    Route::get('/', [EncaminhamentoController::class, 'consultaEncaminhamentos'])->name('listaEncaminhamentos');
    Route::get('/{id}', [EncaminhamentoController::class, 'infoEncaminhamentos'])->name('informacoesEncaminhamentos');
    Route::post('/{id}/gerar-agendamento', [EncaminhamentoController::class, 'gerarAgendamento'])->name('gerarEncaminhamentos');
});

Route::prefix('odontologia/relatorios')->group(function () {
    Route::get('/', [ReportController::class, 'consultaRelatorios'])->name('listaRelatorios');
});

Route::prefix('odontologia')->group(function () {
    Route::get('/disciplinas/{disciplina}/boxes', [EncaminhamentoController::class, 'boxesPorDisciplina']);
    Route::get('/disciplinas/{disciplina}/diasemana', [EncaminhamentoController::class, 'diaSemanaPorDisciplina']);
    Route::get('/disciplinas/{disciplina}/turmas', [EncaminhamentoController::class, 'turmasPorDisciplina']);
    Route::get('/disciplinas/{disciplina}/{turma}/{box}/alunos', [EncaminhamentoController::class, 'alunos']);
});

// RELATORIOS
Route::prefix('odontologia')->group(function () {
    Route::get('/relatorio/agendamentos', [RelatorioAgendamentoController::class, 'Agendamento'])->name('listaAgendamentos');
    Route::get('/relatorio/encaminhamentos', [RelatorioEncaminhamentoController::class, 'Encaminhamento'])->name('listaEncaminhamentos');
    Route::get('/relatorio/financeiro', [RelatorioFinanceiroController::class, 'Financeiro'])->name('listaFinanceiro');
    Route::get('/relatorio/acessos', [RelatorioAcessoController::class, 'Acesso'])->name('listaAcessos');
});


// EDIÇÕES MISC
Route::post('/alterarstatus/{agendaId}', [CalendarioController::class, 'editStatus'])->name('editStatus');
Route::post('/definelocalatendimento/{agendaId,boxId}', [AgendaController::class, 'defineLocalAtendimento'])->name('defineLocalAtendimento');

// VIEWS SEM CONTROLLER — restritas a Usuario
Route::middleware([AuthMiddleware::class . ':Admin,Coordenador'])->group(function () {
    Route::get('/consultarpaciente', function () {
        return view('odontologia/consult_patient');
    })->name('consultarpaciente');

    Route::get('/consultarservico', function () {
        return view('odontologia/consult_servico');
    })->name('consultarservico');

    Route::get('/encaminhamentos', function () {
        return view('odontologia/encaminhamentos');
    })->name('encaminhamentos');

    Route::get('/relatorios', function () {
        return view('odontologia/relatorios');
    })->name('relatorios');

    Route::get('/consultarbox', function () {
        return view('odontologia/consult_box');
    })->name('consultarbox');

    Route::get('/consultarusuario', function () {
        return view('odontologia/consult_user');
    })->name('consultarusuario');

    Route::get('/consultardisciplinabox', function () {
        return view('odontologia/consult_box_discipline');
    })->name('consultardisciplinabox');
});
