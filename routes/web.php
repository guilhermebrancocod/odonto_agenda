<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Odonto\OdontoCreateController;
use App\Http\Controllers\Odonto\OdontoConsultController;

use App\Http\Controllers\Odonto\OdontoUpdateController;
use App\Http\Controllers\Odonto\OdontoDeleteController;

use App\Http\Controllers\LoginController;

use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\CheckClinicaMiddleware;

use App\Http\Controllers\Odonto\PatientController;
use App\Http\Controllers\Odonto\AgendaController;
use App\Http\Controllers\Odonto\UserController;
use App\Http\Controllers\Odonto\ServiceController;
use App\Http\Controllers\Odonto\BoxDisciplineStudentsController;
use App\Http\Controllers\Odonto\BoxesController;
use App\Http\Controllers\Odonto\EncaminhamentoController;
use App\Http\Controllers\Odonto\CalendarioController;

// -------------------- ODONTOLOGIA --------------------

// MENU
Route::get('/odontologia/menu_agenda', function () {
    $usuario = session('usuario');
    return view('odontologia/menu_agenda', compact('usuario'));
})->name('menu_agenda');

// MIDDLEWARE DE ROTAS ODONTOLOGIA
Route::middleware([AuthMiddleware::class, CheckClinicaMiddleware::class])->prefix('odontologia')->group(function () {

    Route::get('/', function () {
        $usuario = session('usuario');
        return view('odontologia/menu_agenda', compact('usuario'));
    })->name('menu_agenda_odontologia');

    Route::get('/usuarios', function () {
        return view('odontologia/usuarios');
    })->name('usuarios_odontologia');

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
        return view('odontologia/create_service');
    })->name('criarservico_odontologia');

    Route::get('/criarusuario', function () {
        return view('odontologia/create_user');
    })->name('criarusuario_odontologia');

    Route::get('/criarboxdisciplina', function () {
        return view('odontologia/create_box_discipline');
    })->name('criarbox_disciplina_odontologia');
});

//USER

Route::post('/odontologia/criarusuario', [BoxesController::class, 'createBox'])->name('createBox');
Route::get('/odontologia/criarusuario/{userId}', [UserController::class, 'editUser'])->name('editUser');
Route::put('/updateUser/{userId}', [UserController::class, 'updateUser'])->name('updateUser');
//LOG
Route::get('/odontologia/pacientes/{id}/audits', [PatientController::class, 'historyPaciente'])->name('pacientes.audit');

// CRIAÇÃO E EDIÇÃO - PACIENTE
Route::get('/odontologia/criarpaciente', [PatientController::class, 'showForm'])->name('criarpaciente');
Route::get('/odontologia/criarpaciente/{pacienteId}', [PatientController::class, 'editPatient'])->name('editPatient');
Route::post('/odontologia/criarpaciente', [PatientController::class, 'fCreatePatient'])->name('createPatient');
Route::put('/updatePatient/{id}', [PatientController::class, 'updatePatient'])->name('updatePatient');
Route::post('/odontologia/criarusuario', [UserController::class, 'createUsuario'])->name('createUsuario');
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

// BOX-DISCIPLINAS
Route::post('/odontologia/criarboxdisciplina', [BoxDisciplineStudentsController::class, 'createBoxDiscipline'])->name('createBoxDiscipline');
Route::get('/odontologia/criarboxdisciplina/{idBoxDiscipline}', [BoxDisciplineStudentsController::class, 'editBoxDiscipline'])->name('editBoxDiscipline');
Route::get('/odontologia/deleteboxdisciplina/{idBoxDiscipline}', [OdontoDeleteController::class, 'deleteBoxDiscipline'])->name('deleteBoxDiscipline');
Route::put('/criarboxdisciplina/{idBoxDiscipline}', [OdontoUpdateController::class, 'updateBoxDiscipline'])->name('updateBoxDiscipline');

// AGENDA
Route::post('/odontologia/criaragenda', [AgendaController::class, 'createAgenda'])->name('createAgenda');
Route::get('/odontologia/criaragenda/{agendaId}', [AgendaController::class, 'editAgenda'])->name('editAgenda');
Route::put('/updateAgenda/{id}', [AgendaController::class, 'updateAgenda'])->name('updateAgenda');

//PERFIL

Route::get('/perfil', [LoginController::class, 'login']);

//CALENDÁRIO
Route::get('/odontologia/agendamentos', [CalendarioController::class, 'getAgendamentos']);
Route::get('/odontologia/agendamentos/alunos-sem-agendamento', [CalendarioController::class, 'getAlunosSemAgendamento']);

// CONSULTAS
Route::get('/odontologia/disciplinascombox/', [OdontoConsultController::class, 'disciplinascombox']);
Route::get('/getBoxDisciplines/{discipline}', [OdontoConsultController::class, 'boxesDisciplina']);
Route::get('/procedimentos', [OdontoConsultController::class, 'procedimento']);
Route::get('/odontologia/turmas/{disciplina}', [OdontoConsultController::class, 'getTodasTurmas']);
Route::get('/odontologia/turmasAgendadas/', [OdontoConsultController::class, 'getTurmasAgendadas']);
Route::get('/odontologia/turmasAgendadas/{turmaSelecionada}', [OdontoConsultController::class, 'getTodasTurmasSelecionada']);
Route::get('/getHorariosBoxDisciplinas/{discipline}', [OdontoConsultController::class, 'getHorariosBoxDisciplinas']);
Route::get('/odontologia/disciplinas/', [OdontoConsultController::class, 'getDisciplinas']);
Route::get('/odontologia/turmas', [OdontoConsultController::class, 'getTurmas']);
Route::get('/odontologia/datas/{disciplina}/{turma}', [OdontoConsultController::class, 'getDatasTurmaDisciplina']);
Route::get('/odontologia/horarios/{disciplina}/{turma}/{diasemana}', [OdontoConsultController::class, 'getHorariosDatasTurmaDisciplina']);
Route::get('/odontologia/alunos/{disciplina}/{turma}', [OdontoConsultController::class, 'getAlunosDisciplinaTurma']);
Route::get('/odontologia/alunos/{disciplina}/{turma}/{box}', [OdontoConsultController::class, 'getAlunosDisciplinaTurmaAgenda']);
Route::get('/odontologia/boxes', [BoxesController::class, 'getBoxes']);
Route::get('/odontologia/user/{userId}', [OdontoConsultController::class, 'getUserId']);
Route::get('/odontologia/boxeservicos/{servicoId}', [OdontoConsultController::class, 'getBoxeServicos']);
Route::get('/getPacientes', [OdontoConsultController::class, 'buscarPacientes']);
Route::get('/getProcedures', [OdontoConsultController::class, 'buscarProcedimentos']);
Route::get('/getAgenda', [OdontoConsultController::class, 'buscarAgendamentos']);
Route::get('/getBoxes', [OdontoConsultController::class, 'buscarBoxes']);
Route::get('/getUser', [OdontoConsultController::class, 'buscarUsuarios']);
Route::get('/getUserLyceum', [OdontoConsultController::class, 'buscarUsuariosLyceum']);
Route::get('/getUserLyceum/{pessoa}', [OdontoConsultController::class, 'buscarPessoaLyceum']);
Route::get('/getBoxDisciplines', [OdontoConsultController::class, 'buscarBoxeDisciplinas']);
Route::get('/consultaboxdisciplina/{idBoxDiscipline}', [OdontoConsultController::class, 'consultaboxdisciplina']);
Route::get('/paciente/{pacienteId}', [OdontoConsultController::class, 'listaPacienteId']);
Route::get('/servicos/{servicoId}', [OdontoConsultController::class, 'listaServicosId']);
Route::get('/agenda/{pacienteId}', [OdontoConsultController::class, 'listaAgendamentoId']);

// CONSULTA DE VIEWS
Route::get('/odontologia/consultarpaciente', [OdontoConsultController::class, 'fSelectPatient'])->name('selectPatient');
Route::get('/odontologia/consultarservico', [OdontoConsultController::class, 'fSelectService'])->name('selectService');
Route::get('/odontologia/encaminhamentos', [OdontoConsultController::class, 'consultaEncaminhamentos'])->name('listaEncaminhamentos');
Route::get('/odontologia/consultarbox', [OdontoConsultController::class, 'fSelectBox'])->name('selectBox');
Route::get('/odontologia/consultarusuario', [OdontoConsultController::class, 'selectUser'])->name('selectUser');
Route::get('/odontologia/consultardisciplinabox', [OdontoConsultController::class, 'fSelectBoxDiscipline'])->name('selectBoxDiscipline');
Route::get('/odontologia/consultaragenda', [OdontoConsultController::class, 'fSelectAgenda'])->name('selectAgenda');

//ENCAMINHAMENTO
Route::prefix('odontologia/encaminhamentos')->group(function () {
    Route::get('/', [EncaminhamentoController::class, 'consultaEncaminhamentos'])->name('listaEncaminhamentos'); // sua lista atual (JSON)
    Route::get('/{id}', [EncaminhamentoController::class, 'infoEncaminhamentos'])->name('informacoesEncaminhamentos'); // detalhes p/ preencher form
    Route::post('/{id}/gerar-agendamento', [EncaminhamentoController::class, 'gerarAgendamento'])->name('informacoesEncaminhamentos'); // efetivar
});

Route::prefix('odontologia')->group(function () {
    Route::get('/disciplinas/{disciplina}/boxes', [EncaminhamentoController::class, 'boxesPorDisciplina']);
    Route::get('/disciplinas/{disciplina}/diasemana', [EncaminhamentoController::class, 'diaSemanaPorDisciplina']);
    Route::get('/disciplinas/{disciplina}/turmas', [EncaminhamentoController::class, 'turmasPorDisciplina']);
    Route::get('/disciplinas/{disciplina}/{turma}/{box}/alunos', [EncaminhamentoController::class, 'alunos']);
});


// EDIÇÕES MISC
Route::post('/alterarstatus/{agendaId}', [OdontoUpdateController::class, 'editStatus'])->name('editStatus');
Route::post('/definelocalatendimento/{agendaId,boxId}', [AgendaController::class, 'defineLocalAtendimento'])->name('defineLocalAtendimento');

// VIEWS SEM CONTROLLER
Route::get('/consultarpaciente', function () {
    return view('odontologia/consult_patient');
})->name('consultarpaciente');

Route::get('/consultarservico', function () {
    return view('odontologia/consult_servico');
})->name('consultarservico');

Route::get('/encaminhamentos', function () {
    return view('odontologia/encaminhamentos');
})->name('encaminhamentos');

Route::get('/consultarbox', function () {
    return view('odontologia/consult_box');
})->name('consultarbox');

Route::get('/consultarusuario', function () {
    return view('odontologia/consult_user');
})->name('consultarusuario');

Route::get('/consultardisciplinabox', function () {
    return view('odontologia/consult_box_discipline');
})->name('consultardisciplinabox');

// -----------------------------------------------------

// PÁGINA DE LOGIN - SELEÇÃO DE PSICOLOGIA OU ODONTOLOGIA
Route::get('/', function () {
    if (session()->has('usuario')) {
        return view('login');
    }

    // $usuario = session('usuario');
    // session(['last_clinic_route' => 'menu_agenda_psicologia']);
    return view('login', compact('usuario'));
})->name('menu_agenda_psicologia');

Route::get('/', function () {
    if (session()->has('usuario')) {
        $usuario = session('usuario');
        $clinicas = $usuario->pluck('ID_CLINICA')->toArray();
        $sit_usuario = session('SIT_USUARIO');

        if (in_array(2, $clinicas)) {
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
            return redirect('/');
        }
        return view('login');
    })->name('loginGET');

    Route::post('/login', [LoginController::class, 'login'])->name('loginPOST');

    Route::get('/logout', function () {
        session()->forget('usuario');
        return redirect()->route('loginGET');
    })->name('logout');
});