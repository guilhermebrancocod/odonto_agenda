<?php

namespace App\Http\Controllers\Odonto\Relatorios;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioAcessoController extends Controller
{
    public function Acesso(Request $request)
    {
        $data_ini      = $request->input('data_ini');       
        $data_fim      = $request->input('data_fim');        
        $status        = $request->input('status');         
        $filtroUsuario = $request->input('filtroUsuario');  

        // ---- Helpers de data (aceita d/m/Y ou Y-m-d) ----
        $parseDateOnly = function (?string $d): ?Carbon {
            if (!$d) return null;
            if (str_contains($d, '/')) {
                return Carbon::createFromFormat('d/m/Y', $d, 'America/Sao_Paulo')->startOfDay();
            }
            return Carbon::parse($d, 'America/Sao_Paulo')->startOfDay();
        };

        $ini = $parseDateOnly($data_ini);
        $fim = $parseDateOnly($data_fim);
        if ($ini && $fim && $ini->gt($fim)) {
            [$ini, $fim] = [$fim, $ini];
        }

        // ---- Expressões JSON ----
        $jsonNome     = "JSON_VALUE(NEW_VALUES, '$.NOME')";
        $jsonDtLogin  = "JSON_VALUE(NEW_VALUES, '$.DATA_LOGIN')";
        $jsonHrLogin  = "JSON_VALUE(NEW_VALUES, '$.HORA_LOGIN')";
        $jsonDtLogout = "JSON_VALUE(NEW_VALUES, '$.DATA_LOGOUT')";
        $jsonHrLogout = "JSON_VALUE(NEW_VALUES, '$.HORA_LOGOUT')";

        // ---- Datas/horas e ordenação ----
        $dtLoginExpr   = "TRY_CONVERT(datetime, CONCAT($jsonDtLogin,  ' ', $jsonHrLogin))";
        $dtLogoutExpr  = "TRY_CONVERT(datetime, CONCAT($jsonDtLogout, ' ', $jsonHrLogout))";
        $dtOrderExpr   = "COALESCE($dtLogoutExpr, $dtLoginExpr)";     // p/ ordernar
        $dateLoginOnly = "TRY_CONVERT(date, $jsonDtLogin)";
        $dateLogoutOnly = "TRY_CONVERT(date, $jsonDtLogout)";

        // ---- Query base (sempre a mesma) ----
        $q = DB::table('FAESA_CLINICA_ODONTOLOGIA_AUDITORIA')
            ->whereRaw('ISJSON(NEW_VALUES) = 1')
            ->where('USER_NAME', '<>', '')
            ->selectRaw("
            AUDITABLE_ID AS CODIGO_USUARIO,
            USER_NAME,
            $jsonNome       AS NOME,
            EVENT,
            $jsonDtLogin    AS DATA_LOGIN,
            $jsonHrLogin    AS HORA_LOGIN,
            $jsonDtLogout   AS DATA_LOGOUT,
            $jsonHrLogout   AS HORA_LOGOUT
        ");

        // ---- Filtro por período (somente DATA; login usa DATA_LOGIN, logout usa DATA_LOGOUT) ----
        if ($ini || $fim) {
            // limites (DATE, não DATETIME)
            $iniDate = $ini ? $ini->toDateString() : null;      // 'YYYY-MM-DD'
            $fimDate = $fim ? $fim->copy()->endOfDay()->toDateString() : null;

            if ($iniDate && $fimDate) {
                $q->whereRaw("
                (
                  (EVENT = 'login'  AND $dateLoginOnly  BETWEEN ? AND ?)
               OR (EVENT = 'logout' AND $dateLogoutOnly BETWEEN ? AND ?)
                )
            ", [$iniDate, $fimDate, $iniDate, $fimDate]);
            } elseif ($iniDate) {
                $q->whereRaw("
                (
                  (EVENT = 'login'  AND $dateLoginOnly  >= ?)
               OR (EVENT = 'logout' AND $dateLogoutOnly >= ?)
                )
            ", [$iniDate, $iniDate]);
            } else { // só $fimDate
                $q->whereRaw("
                (
                  (EVENT = 'login'  AND $dateLoginOnly  <= ?)
               OR (EVENT = 'logout' AND $dateLogoutOnly <= ?)
                )
            ", [$fimDate, $fimDate]);
            }
        }

        // ---- Filtro por evento/status ('login' | 'logout') ----
        if (!empty($status)) {
            $q->where('EVENT', $status);
        }

        // ---- Filtro por usuário: numérico = AUDITABLE_ID; texto = NOME (JSON) ----
        if (!empty($filtroUsuario)) {
            if (is_numeric($filtroUsuario)) {
                $q->where('AUDITABLE_ID', (int) $filtroUsuario);
            } else {
                $q->whereRaw("$jsonNome LIKE ?", ['%' . $filtroUsuario . '%']);
            }
        }

        // ---- Ordenação: mais recente primeiro (logout > login) ----
        $acessos = $q->orderByRaw("$dtOrderExpr DESC")->get();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($acessos);
        }

        return view('odontologia/relatorio/acessos', ['listaAcessos' => $acessos]);
    }
}
