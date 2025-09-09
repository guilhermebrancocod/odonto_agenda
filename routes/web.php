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
use App\Http\Controllers\Psicologia\ClinicaController;
use App\Http\Controllers\Psicologia\SalaController;
use App\Http\Controllers\Psicologia\HorarioController;
use App\Http\Controllers\Psicologia\DisciplinaController;
use App\Http\Controllers\Psicologia\PsicologoController;
use App\Models\FaesaClinicaServico;
use App\Models\FaesaClinicaPaciente;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\AuthProfessorMiddleware;
use App\Http\Middleware\CheckClinicaMiddleware;
use App\Http\Middleware\AuthPsicologoMiddleware;
use App\Services\Psicologia\PacienteService;

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

    Route::get('/criarbox', function () {
        return view('odontologia/create_box');
    })->name('criarbox_odontologia');

    Route::get('/criarboxdisciplina', function () {
        return view('odontologia/create_box_discipline');
    })->name('criarbox_disciplina_odontologia');
});

// CRIAÇÃO E EDIÇÃO - PACIENTE
Route::get('/odontologia/criarpaciente', [OdontoCreateController::class, 'showForm'])->name('criarpaciente');
Route::get('/odontologia/criarpaciente/{pacienteId}', [OdontoCreateController::class, 'editPatient'])->name('editPatient');
Route::post('/odontologia/criarpaciente', [OdontoCreateController::class, 'fCreatePatient'])->name('createPatient');
Route::put('/updatePatient/{id}', [OdontoUpdateController::class, 'updatePatient'])->name('updatePatient');

// SERVIÇOS
Route::get('/odontologia/criarservico', function () {
    return view('odontologia/create_service');
})->name('criarservico');
Route::post('/odontologia/criarservico', [OdontoCreateController::class, 'createService'])->name('createService');
Route::get('/criarservico/{idService}', [OdontoCreateController::class, 'editService'])->name('editService');
Route::put('/criarservico/{idService}', [OdontoUpdateController::class, 'updateService'])->name('updateService');

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

// CONSULTAS
Route::get('/odontologia/agendamentos', [OdontoConsultController::class, 'getAgendamentos']);
Route::get('/odontologia/disciplinas', [OdontoConsultController::class, 'getDisciplinas']);
Route::get('/odontologia/boxes', [OdontoConsultController::class, 'getBoxes']);
Route::get('/odontologia/boxes/{boxId}', [OdontoConsultController::class, 'getBoxesId']);
Route::get('/odontologia/boxeservicos/{servicoId}', [OdontoConsultController::class, 'getBoxeServicos']);
Route::get('/getPacientes', [OdontoConsultController::class, 'buscarPacientes']);
Route::get('/getServices', [OdontoConsultController::class, 'buscarServicos']);
Route::get('/getAgenda', [OdontoConsultController::class, 'buscarAgendamentos']);
Route::get('/getBoxes', [OdontoConsultController::class, 'buscarBoxes']);
Route::get('/getBoxDisciplines', [OdontoConsultController::class, 'buscarBoxeDisciplinas']);
Route::get('/getBoxDisciplines/{discipline}', [OdontoConsultController::class, 'boxesDisciplina']);
Route::get('/consultaboxdisciplina/{idBoxDiscipline}', [OdontoConsultController::class, 'consultaboxdisciplina']);
Route::get('/paciente/{pacienteId}', [OdontoConsultController::class, 'listaPacienteId']);
Route::get('/servicos/{servicoId}', [OdontoConsultController::class, 'listaServicosId']);
Route::get('/servicos', [OdontoConsultController::class, 'services']);
Route::get('/agenda/{pacienteId}', [OdontoConsultController::class, 'listaAgendamentoId']);

// CONSULTA DE VIEWS
Route::get('/odontologia/consultarpaciente', [OdontoConsultController::class, 'fSelectPatient'])->name('selectPatient');
Route::get('/odontologia/consultarservico', [OdontoConsultController::class, 'fSelectService'])->name('selectService');
Route::get('/odontologia/consultarbox', [OdontoConsultController::class, 'fSelectBox'])->name('selectBox');
Route::get('/odontologia/consultardisciplinabox', [OdontoConsultController::class, 'fSelectBoxDiscipline'])->name('selectBoxDiscipline');
Route::get('/odontologia/consultaragenda', [OdontoConsultController::class, 'fSelectAgenda'])->name('selectAgenda');

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
Route::get('/consultarbox', function () {
    return view('odontologia/consult_box');
})->name('consultarbox');
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

Route::get('/selecionar-clinica', function () {
    if(session()->has('usuario')) {
        return view('selecionar_clinica');
    } else {
        return redirect()->route('loginGET');
    }
})->name('selecionar-clinica-get');

Route::middleware([AuthMiddleware::class])->group(function () {

    Route::get('/login', function () {
        if (session()->has('usuario')) {
            return redirect('/');
        }
        return view('login');
    })->name('loginGET');

    Route::post('/login', [LoginController::class, 'login'])->name('loginPOST');

    Route::get('/logout', function() {
        session()->forget('usuario');
        return redirect()->route('loginGET');
    })->name('logout');

    Route::post('/selecionar-clinica', [ClinicaController::class, 'selecionarClinica'])->name('selecionar-clinica-post');
});

/*
|--------------------------------------------------------------------------
| Rotas de Psicologia (Administrativo e Autenticação)
|--------------------------------------------------------------------------
| Unifica todas as rotas sob o prefixo 'psicologia'.
| - O grupo externo aplica o middleware de autenticação geral.
| - O grupo interno aninhado adiciona o middleware específico da clínica para o painel.
*/
Route::prefix('psicologia')
    ->middleware([AuthMiddleware::class])
    ->group(function () {

        // --- Rotas de Autenticação (não precisam do CheckClinicaMiddleware) ---
        Route::get('/login', function () {
            if (session()->has('usuario')) {
                return redirect('/');
            }
            return view('login');
        })->name('loginGET');

        Route::post('/login', function () {
            if (session()->has('usuario')) {
                return redirect()->route('menu_agenda_psicologia');
            }
        })->name('loginPOST');

        Route::get('/logout', function () {
            session()->forget('usuario');
            return redirect()->route('loginGET');
        })->name('logout');

        // --- Painel Administrativo (requer AuthMiddleware E CheckClinicaMiddleware) ---
        Route::middleware([CheckClinicaMiddleware::class])
            ->group(function () {

                // --- MENU E RELATÓRIOS ---
                Route::get('/', function () {
                    $usuario = session('usuario');
                    return view('psicologia.adm/menu_agenda', compact('usuario'));
                })->name('menu_agenda_psicologia');

                Route::view('/relatorios-agendamento', 'psicologia.adm/relatorios_agendamento')
                    ->name('relatorio_psicologia');

                // --- PACIENTES ---
                Route::view('/criar-paciente', 'psicologia.adm/criar_paciente')->name('criarpaciente_psicologia');
                Route::post('/criar-paciente/criar', [PacienteController::class, 'createPaciente'])->name('criarPaciente-Psicologia');

                Route::view('/editar-paciente', 'psicologia.adm/editar_paciente')->name('editarPacienteView-Psicologia');
                Route::post('/editar-paciente/{id}', [PacienteController::class, 'editarPaciente'])->name('editarPaciente-Psicologia');

                Route::view('/consultar-paciente', 'psicologia.adm.consultar_paciente')->name('consultar-paciente');
                Route::get('/consultar-paciente/buscar', [PacienteController::class, 'getPaciente'])->name('getPaciente');
                Route::get('/consultar-paciente/buscar-nome-cpf', [PacienteController::class, 'getPacienteByNameCpf'])->name('getPacienteByNameCpf');
                Route::get('/paciente/{id}/ativar', [PacienteController::class, 'setAtivo'])->name('ativarPaciente-Psicologia');
                Route::delete('/excluir-paciente/{id}', [PacienteController::class, 'deletePaciente'])->name('deletePaciente-Psicologia');
                Route::get('/api/buscar-pacientes', [PacienteController::class, 'apiBuscarPacientes']); // Lógica movida para Controller

                // --- AGENDAMENTOS ---
                Route::view('/criar-agendamento', 'psicologia.adm/criar_agenda')->name('criaragenda_psicologia');
                Route::post('/criar-agendamento/criar', [AgendamentoController::class, 'criarAgendamento'])->name('criarAgendamento-Psicologia');

                Route::view('/consultar-agendamento', 'psicologia.adm.consultar_agendamento')->name('listagem-agendamentos');
                Route::post('/consultar-agendamento/consultar', [AgendamentoController::class, 'getAgendamento'])->name('getAgendamento');

                Route::get('/get-agendamento', [AgendamentoController::class, 'getAgendamento']);
                Route::get('/agendamentos/paciente/{id}', [AgendamentoController::class, 'getAgendamentosByPaciente']);
                Route::get('/agendamento/{id}', [AgendamentoController::class, 'showAgendamento'])->name('agendamento.show');
                Route::get('/agendamento/{id}/editar', [AgendamentoController::class, 'editAgendamento'])->name('agendamento.edit');
                Route::put('/agendamento', [AgendamentoController::class, 'updateAgendamento'])->name('agendamento.update');
                Route::delete('/agendamento/{id}', [AgendamentoController::class, 'deleteAgendamento'])->name('psicologia.agendamento.delete');
                Route::put('/agendamentos/{id}/status', [AgendamentoController::class, 'atualizarStatus']);
                Route::put('/agendamentos/{id}/mensagem-cancelamento', [AgendamentoController::class, 'addMensagemCancelamento']);
                Route::get('/agendamentos-calendar/adm', [AgendamentoController::class, 'getAgendamentosForCalendar']);

                // --- SERVIÇOS ---
                Route::view('/criar-servico', 'psicologia.adm/criar_servico')->name('criarservico_psicologia');
                Route::post('/criar-servico/criar', [ServicoController::class, 'criarServico'])->name('criarServico-Psicologia');

                Route::get('/pesquisar-servico', [ServicoController::class, 'getServicos'])->name('pesquisarServico-Psicologia');
                Route::get('/servicos', [ServicoController::class, 'getServicos']);
                Route::get('/servicos/{id}', [ServicoController::class, 'getServicoById']);
                Route::post('/servicos', [ServicoController::class, 'criarServico']);
                Route::put('/servicos/{id}', [ServicoController::class, 'atualizarServico']);
                Route::delete('/servicos/{id}', [ServicoController::class, 'deletarServico']);
                Route::get('/api/buscar-servicos', [ServicoController::class, 'apiBuscarServicos']); // Lógica movida para Controller

                // --- SALAS ---
                Route::view('/criar-sala', 'psicologia.adm.criar_sala')->name('salas_psicologia');
                Route::post('/salas/criar', [SalaController::class, 'createSala'])->name('criarSala-Psicologia');
                Route::get('/salas/listar', [SalaController::class, 'listSalas'])->name('listarSalas-Psicologia');
                Route::put('/salas/{id}', [SalaController::class, 'updateSala'])->name('atualizarSala-Psicologia');
                Route::delete('salas/{id}', [SalaController::class, 'deleteSala'])->name('deleteSala-Psicologia');
                Route::get('/pesquisar-local', [SalaController::class, 'getSala'])->name('pesquisarLocal-Psicologia');

                // --- HORÁRIOS ---
                Route::view('/criar-horario', 'psicologia.adm.criar_horario')->name('criarHorarioView-Psicologia');
                Route::post('/horarios/criar-horario', [HorarioController::class, 'createHorario'])->name('criarHorario-Psicologia');
                Route::get('/horarios/listar', [HorarioController::class, 'listHorarios'])->name('listarHorarios-Psicologia');
                Route::put('/horarios/atualizar/{id}', [HorarioController::class, 'updateHorario'])->name('updateHorario-Psicologia');
                Route::delete('/horarios/deletar/{id}', [HorarioController::class, 'deleteHorario'])->name('deleteHorario-Psicologia');

                // --- DISCIPLINAS E PSICÓLOGOS ---
                Route::get('/disciplinas-psicologia', [DisciplinaController::class, 'getDisciplina']);
                Route::get('/disciplina/{codigo}', [DisciplinaController::class, 'getDisciplinaByCodigo'])->name('getDisciplinaByCodigo');
                Route::get('/listar-psicologos', [PsicologoController::class, 'listAlunos']);
                Route::get('buscar-aluno/{matricula}', [PsicologoController::class, 'listAlunos'])->name('listAlunos-Psicologia');
            });
    });

/*
|--------------------------------------------------------------------------
| Rotas do Painel do Aluno (Psicólogo)
|--------------------------------------------------------------------------
*/
Route::prefix('aluno')
    ->middleware([AuthPsicologoMiddleware::class])
    ->group(function () {

        // --- AUTENTICAÇÃO E MENU ---
        Route::get('/login', function () {
            if (session()->has('psicologo')) {
                return redirect()->route('psicologoAgenda');
            }
            return view('psicologia.psicologo.login_psicologo');
        })->name('psicologoLoginGet');

        Route::post('/login', fn() => redirect()->route('psicologoAgenda'))->name('psicologoLoginPost');

        Route::get('/logout', function () {
            session()->forget('psicologo');
            return redirect()->route('psicologoLoginGet');
        })->name('psicologoLogout');
        
        Route::get('/', function () {
            if (session()->has('psicologo')) {
                return view('psicologia.psicologo.menu_agenda');
            }
            return redirect()->route('psicologoLoginGet');
        })->name('psicologoAgenda');
        
        // --- FUNCIONALIDADES ---
        Route::view('/criar-agendamento', 'psicologia.psicologo.criar_agenda')->name('psicologoCriarAgenda-Get');
        Route::post('/criar-agendamento/criar', [AgendamentoController::class, 'criarAgendamentoPsicologo'])->name('criarAgendamento-Psicologo');

        Route::view('/consultar-agendamento', 'psicologia.psicologo.consultar_agenda')->name('psicologoConsultarAgendamentos-GET');
        Route::get('/consultar-agendamento/buscar', [AgendamentoController::class, 'getAgendamentosForPsicologo'])->name('getAgendamentosForPsicologo');
            
        Route::get('/agendamentos-calendar', [AgendamentoController::class, 'getAgendamentosForCalendarPsicologo']);
        Route::get('/agendamento/{id}/editar', [AgendamentoController::class, 'editAgendamentoPsicologo'])->name('agendamentoPsicologo.edit');
        Route::get('/consultar-paciente/buscar', [PacienteController::class, 'getPacienteByNameCPFPsicologo'])->name('psicologoGetPaciente');
        Route::get('/pesquisar-disciplina', [ServicoController::class, 'getDisciplinaServico'])->name('psicologoGetDisciplina');
    });

/*
|--------------------------------------------------------------------------
| Rotas do Painel do Professor
|--------------------------------------------------------------------------
*/
Route::prefix('professor')
    ->middleware([AuthProfessorMiddleware::class])
    ->group(function () {
    
        // --- AUTENTICAÇÃO E MENU ---
        Route::get('/', fn() => redirect()->route('professorMenu'));

        Route::get('/login', function () {
            if (session()->has('professor')) {
                return view('psicologia.professor.menu_agenda');
            }
            return view('psicologia.professor.login_professor');
        })->name('professorLoginGet');
        
        Route::post('/login', function () {
            if (session()->has('professor')) {
                return view('psicologia.professor.menu_agenda');
            }
        })->name('professorLoginPost');
        
        Route::match(['get', 'post'], '/professor', fn() => view('psicologia.professor.menu_agenda'))->name('professorMenu');

        Route::get('/logout', function () {
            session()->forget('professor');
            return redirect()->route('professorLoginGet');
        })->name('professorLogout');

        // --- FUNCIONALIDADES ---
        Route::view('/consultar-agendamento', 'psicologia.professor.consultar_agenda')->name('professorConsultarAgendamentos-GET');
        
        Route::get('/agendamentos-calendar', [AgendamentoController::class, 'getAgendamentosForCalendarProfessor'])->name('getAgendamentosForCalendarProfessor');

        Route::get('/consultar-agendamento/buscar', [AgendamentoController::class, 'getAgendamentosForProfessor'])->name('getAgendamentosForProfessor');

        Route::view('/psicologo', 'psicologia.professor.consultar_psicologo');
    });