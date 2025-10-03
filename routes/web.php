<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Odonto\OdontoCreateController;
use App\Http\Controllers\Odonto\OdontoConsultController;
use App\Http\Controllers\Odonto\OdontoUpdateController;
use App\Http\Controllers\Odonto\OdontoDeleteController;
use App\Http\Controllers\Psicologia\PacienteController;
use App\Http\Controllers\Psicologia\AgendamentoController;
use App\Http\Controllers\Psicologia\ServicoController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Psicologia\AlunoController;
use App\Http\Controllers\Psicologia\ClinicaController;
use App\Http\Controllers\Psicologia\SalaController;
use App\Http\Controllers\Psicologia\HorarioController;
use App\Http\Controllers\Psicologia\DisciplinaController;
use App\Models\FaesaClinicaServico;
use App\Models\FaesaClinicaPaciente;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\AuthProfessorMiddleware;
use App\Http\Middleware\CheckClinicaMiddleware;
use App\Http\Middleware\AuthAlunoMiddleware;

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

Route::post('/odontologia/criarusuario', [OdontoCreateController::class, 'createBox'])->name('createBox');
Route::get('/odontologia/criarusuario/{userId}', [OdontoCreateController::class, 'editUser'])->name('editUser');
Route::put('/updateUser/{userId}', [OdontoUpdateController::class, 'updateUser'])->name('updateUser');


//LOG
Route::get('/odontologia/pacientes/{id}/audits', [OdontoCreateController::class, 'historyPaciente'])->name('pacientes.audit');

// CRIAÇÃO E EDIÇÃO - PACIENTE
Route::get('/odontologia/criarpaciente', [OdontoCreateController::class, 'showForm'])->name('criarpaciente');
Route::get('/odontologia/criarpaciente/{pacienteId}', [OdontoCreateController::class, 'editPatient'])->name('editPatient');
Route::post('/odontologia/criarpaciente', [OdontoCreateController::class, 'fCreatePatient'])->name('createPatient');
Route::put('/updatePatient/{id}', [OdontoUpdateController::class, 'updatePatient'])->name('updatePatient');
Route::post('/odontologia/criarusuario', [OdontoCreateController::class, 'createUsuario'])->name('createUsuario');
Route::put('/updateUser/{id}', [OdontoUpdateController::class, 'updateUser'])->name('updateUser');

// SERVIÇOS
Route::get('/odontologia/criarservico', function () {
    return view('odontologia/create_service');
})->name('criarservico');
Route::post('/odontologia/criarservico', [OdontoCreateController::class, 'createProcedures'])->name('createProcedures');
Route::get('/criarservico/{idService}', [OdontoCreateController::class, 'editService'])->name('editService');
Route::put('/criarservico/{idService}', [OdontoUpdateController::class, 'updateProcedures'])->name('updateProcedures');

// BOXES
Route::get('/odontologia/criarbox', function () {
    return view('odontologia/create_box');
})->name('criarbox');
Route::post('/odontologia/criarbox', [OdontoCreateController::class, 'createBox'])->name('createBox');
Route::get('/odontologia/criarbox/{boxId}', [OdontoCreateController::class, 'editBox'])->name('editBox');
Route::put('/criarbox/{boxId}', [OdontoUpdateController::class, 'updateBox'])->name('updateBox');

// BOX-DISCIPLINAS
Route::post('/odontologia/criarboxdisciplina', [OdontoCreateController::class, 'createBoxDiscipline'])->name('createBoxDiscipline');
Route::get('/odontologia/criarboxdisciplina/{idBoxDiscipline}', [OdontoCreateController::class, 'editBoxDiscipline'])->name('editBoxDiscipline');
Route::get('/odontologia/deleteboxdisciplina/{idBoxDiscipline}', [OdontoDeleteController::class, 'deleteBoxDiscipline'])->name('deleteBoxDiscipline');
Route::put('/criarboxdisciplina/{idBoxDiscipline}', [OdontoUpdateController::class, 'updateBoxDiscipline'])->name('updateBoxDiscipline');

// AGENDA
Route::post('/odontologia/criaragenda', [OdontoCreateController::class, 'fCreateAgenda'])->name('createAgenda');
Route::get('/odontologia/criaragenda/{agendaId}', [OdontoCreateController::class, 'editAgenda'])->name('editAgenda');
Route::put('/updateAgenda/{id}', [OdontoUpdateController::class, 'updateAgenda'])->name('updateAgenda');

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
Route::get('/odontologia/boxes', [OdontoConsultController::class, 'getBoxes']);
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
    Route::get('/disciplinas/{disciplina}/turmas',[EncaminhamentoController::class,'turmasPorDisciplina']);
    Route::get('/disciplinas/{disciplina}/{turma}/{box}/alunos',[EncaminhamentoController::class,'alunos']);
});


// EDIÇÕES MISC
Route::post('/alterarstatus/{agendaId}', [OdontoUpdateController::class, 'editStatus'])->name('editStatus');
Route::post('/definelocalatendimento/{agendaId,boxId}', [OdontoCreateController::class, 'defineLocalAtendimento'])->name('defineLocalAtendimento');

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

    Route::post('/selecionar-clinica', [ClinicaController::class, 'selecionarClinica'])->name('selecionar-clinica-post');
});

Route::middleware([AuthMiddleware::class, CheckClinicaMiddleware::class])
    ->prefix('psicologia')
    ->group(function () {

        // ROOT
        Route::get('/', function () {
            $usuario = session('usuario');
            return view('psicologia.adm/menu_agenda', compact('usuario'));
        })->name('menu_agenda_psicologia');

        // RELATÓRIOS
        Route::get('/relatorios-agendamento', function () {
            return view('psicologia.adm/relatorios_agendamento');
        })->name('relatorio_psicologia');

        // CRIAR PACIENTE
        Route::get('/criar-paciente', function () {
            return view('psicologia.adm/criar_paciente');
        })->name('criarpaciente_psicologia');
        Route::post('/criar-paciente/criar', [PacienteController::class, 'createPaciente'])->name('criarPaciente-Psicologia');

        // EDITAR PACIENTE
        Route::get('/editar-paciente', function () {
            return view('psicologia.adm/editar_paciente');
        })->name('editarPacienteView-Psicologia');
        Route::post('/editar-paciente/{id}', [PacienteController::class, 'editarPaciente'])->name('editarPaciente-Psicologia');

        // API BUSCAR PACIENTES
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

        // CONSULTAR PACIENTE
        Route::get('/consultar-paciente', function () {
            return view('psicologia.adm.consultar_paciente');
        })->name('consultar-paciente');
        Route::get('/consultar-paciente/buscar', [PacienteController::class, 'getPaciente'])->name('getPaciente');
        Route::get('/consultar-paciente/buscar-nome-cpf', [PacienteController::class, 'getPacienteByNameCpf'])->name('getPacienteByNameCpf');
        Route::get('/paciente/{id}/ativar', [PacienteController::class, 'setAtivo'])->name('ativarPaciente-Psicologia');
        Route::delete('/excluir-paciente/{id}', [PacienteController::class, 'deletePaciente'])->name('deletePaciente-Psicologia');

        // AGENDAMENTOS
        Route::get('/criar-agendamento', function () {
            return view('psicologia.adm/criar_agenda');
        })->name('criaragenda_psicologia');
        Route::post('/criar-agendamento/criar', [AgendamentoController::class, 'criarAgendamento'])->name('criarAgendamento-Psicologia');
        Route::put('/agendamentos/{id}/status', [AgendamentoController::class, 'atualizarStatus']);
        Route::put('/agendamentos/{id}/mensagem-cancelamento', [AgendamentoController::class, 'addMensagemCancelamento']);
        Route::get('/consultar-agendamento', function () {
            return view('psicologia.adm.consultar_agendamento');
        })->name('listagem-agendamentos');
        Route::post('/consultar-agendamento/consultar', [AgendamentoController::class, 'getAgendamento'])->name('getAgendamento');
        Route::get('/get-agendamento', [AgendamentoController::class, 'getAgendamento']);
        Route::get('/agendamentos/paciente/{id}', [AgendamentoController::class, 'getAgendamentosByPaciente']);
        Route::get('/agendamento/{id}', [AgendamentoController::class, 'showAgendamento'])->name('agendamento.show');
        Route::get('/agendamentos-calendar/adm', [AgendamentoController::class, 'getAgendamentosForCalendar']);
        Route::get('/agendamento/{id}/editar', [AgendamentoController::class, 'editAgendamento'])->name('agendamento.edit');
        Route::put('/agendamento', [AgendamentoController::class, 'updateAgendamento'])->name('agendamento.update');
        Route::delete('/agendamento/{id}', [AgendamentoController::class, 'deleteAgendamento'])->name('psicologia.agendamento.delete');

        Route::get('buscar-aluno/{matricula}', [AlunoController::class, 'listAlunos'])->name('listAlunos-Psicologia');

        // SERVIÇOS
        Route::get('/criar-servico', function () {
            return view('psicologia.adm/criar_servico');
        })->name('criarservico_psicologia');
        Route::post('/criar-servico/criar', [ServicoController::class, 'criarServico'])->name('criarServico-Psicologia');
        Route::get('/pesquisar-servico', [ServicoController::class, 'getServicos'])->name('pesquisarServico-Psicologia');
        Route::get('/api/buscar-servicos', function () {
            $query = request()->input('query', '');
            $servicos = FaesaClinicaServico::where('SERVICO_CLINICA_DESC', 'like', "%{$query}%")
                ->where('ID_CLINICA', 1)
                ->limit(10)
                ->get(['ID_SERVICO_CLINICA', 'SERVICO_CLINICA_DESC']);

            return response()->json($servicos);
        });
        Route::get('/servicos', [ServicoController::class, 'getServicos']);
        Route::get('/servicos/{id}', [ServicoController::class, 'getServicoById']);
        Route::post('/servicos', [ServicoController::class, 'criarServico']);
        Route::put('/servicos/{id}', [ServicoController::class, 'atualizarServico']);
        Route::delete('/servicos/{id}', [ServicoController::class, 'deletarServico']);

        // SALAS
        Route::get('/criar-sala', function () {
            return view('psicologia.adm.criar_sala');
        })->name('salas_psicologia');
        Route::post('/salas/criar', [SalaController::class, 'createSala'])->name('criarSala-Psicologia');
        Route::get('/salas/listar', [SalaController::class, 'listSalas'])->name('listarSalas-Psicologia');
        Route::put('/salas/{id}', [SalaController::class, 'updateSala'])->name('atualizarSala-Psicologia');
        Route::get('/pesquisar-local', [SalaController::class, 'getSala'])->name('pesquisarLocal-Psicologia');
        Route::delete('salas/{id}', [SalaController::class, 'deleteSala'])->name('deleteSala-Psicologia');

        // HORÁRIOS
        Route::get('/criar-horario', function () {
            return view('psicologia.adm.criar_horario');
        })->name('criarHorarioView-Psicologia');
        Route::post('/horarios/criar-horario', [HorarioController::class, 'createHorario'])->name('criarHorario-Psicologia');
        Route::get('/horarios/listar', [HorarioController::class, 'listHorarios'])->name('listarHorarios-Psicologia');
        Route::put('/horarios/atualizar/{id}', [HorarioController::class, 'updateHorario'])->name('updateHorario-Psicologia');
        Route::delete('/horarios/deletar/{id}', [HorarioController::class, 'deleteHorario'])->name('deleteHorario-Psicologia');

        // BUSCA DE DISCIPLINAS PARA VINCULAR AO SERVIÇO
        Route::get('/disciplinas-psicologia', [DisciplinaController::class, 'getDisciplina']);
        Route::get('/disciplina/{codigo}', [DisciplinaController::class, 'getDisciplinaByCodigo'])->name('getDisciplinaByCodigo');

        Route::get('/listar-alunos', [AlunoController::class, 'listAlunos']);
    });


// ROTAS DE ALUNO
Route::get('/aluno/login', function () {
    if (session()->has('aluno')) {
        return redirect()->route('alunoAgenda');
    } else {
        return view('psicologia.aluno.login_aluno');
    }
})->name('alunoLoginGet');

Route::get('/aluno', function () {
    if (session()->has('aluno')) {
        return view(view: 'psicologia.aluno.menu_agenda');
    } else {
        return redirect()->route('alunoLoginGet');
    }
})->name('alunoAgenda');

Route::middleware([AuthAlunoMiddleware::class])->group(function () {
    Route::post('/aluno/login', function () {
        return redirect()->route('alunoAgenda');
        // return view('psicologia.aluno.menu_agenda');
    })->name('alunoLoginPost');

    Route::get('/aluno/agendamentos-calendar', [AgendamentoController::class, 'getAgendamentosForCalendaraluno']);

    Route::get('/aluno/consultar-paciente/buscar', [PacienteController::class, 'getPacienteByNameCPFaluno'])->name('alunoGetPaciente');

    Route::get('/aluno/consultar-agendamento/buscar', [AgendamentoController::class, 'getAgendamentosForAluno'])->name('getAgendamentosForAluno');

    Route::get('/aluno/agendamento/{id}/editar', [AgendamentoController::class, 'editAgendamentoaluno'])->name('agendamentoaluno.edit');

    Route::put('/aluno/agendamento', [AgendamentoController::class, 'updateAgendamento'])->name('aluno.agendamento.update');

    Route::get('/aluno/pesquisar-disciplina', [ServicoController::class, 'getDisciplinaServico'])->name('alunoGetDisciplina');

    Route::get('/aluno/criar-agendamento', function () {
        return view('psicologia.aluno.criar_agenda');
    })->name('alunoCriarAgenda-Get');

    Route::post('/aluno/criar-agendamento/criar', [AgendamentoController::class, 'criarAgendamentoaluno'])->name('criarAgendamento-aluno');

    Route::get('/aluno/consultar-agendamento', function () {
        return view('psicologia.aluno.consultar_agenda');
    })->name('alunoConsultarAgendamentos-GET');
});
Route::get('/aluno/logout', function () {
    session()->forget('aluno');
    return redirect()->route('alunoLoginGet');
})->name('alunoLogout');





// ROTAS DE PROFESSOR
Route::get('/professor/login', function () {
    if (session()->has('professor')) {
        return view('psicologia.professor.menu_agenda');
    } else {
        return view('psicologia.professor.login_professor');
    }
})->name('professorLoginGet');

Route::post('/professor/login', function () {
    if (session()->has('professor')) {
        return view('psicologia.professor.menu_agenda');
    }
})->name('professorLoginPost');

Route::middleware([AuthProfessorMiddleware::class])->group(function () {
    Route::match(['get', 'post'], '/professor', function () {
        return view('psicologia.professor.menu_agenda');
    })->name('professorMenu');

    Route::get('/professor/agendamentos-calendar', [AgendamentoController::class, 'getAgendamentosForCalendarProfessor'])->name('getAgendamentosForCalendarProfessor');

    Route::post('/professor/login', function () {
        return view('psicologia.professor.menu_agenda');
    })->name('professorLoginPost');

    Route::get('/professor/consultar-agendamento', function () {
        return view('psicologia.professor.consultar_agenda');
    })->name('professorConsultarAgendamentos-GET');

    Route::get('/professor/consultar-agendamento/buscar', [AgendamentoController::class, 'getAgendamentosForProfessor'])->name('getAgendamentosForProfessor');

    Route::get('/professor/aluno', function () {
        return view('psicologia.professor.consultar_aluno');
    });

    Route::get('/professor/logout', function () {
        session()->forget('professor');
        return redirect()->route('professorLoginGet');
    })->name('professorLogout');
});
