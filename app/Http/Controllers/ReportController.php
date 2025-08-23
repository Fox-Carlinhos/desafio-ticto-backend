<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{

    public function timeRecordsReport(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
            'employee_id' => 'nullable|integer|exists:employees,id',
            'manager_id' => 'nullable|integer|exists:users,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $perPage = $request->get('per_page', 20);
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        $whereConditions = [];
        $bindings = [];

        if ($request->filled('start_date')) {
            $whereConditions[] = "tr.recorded_at >= ?";
            $bindings[] = $request->start_date . ' 00:00:00';
        }

        if ($request->filled('end_date')) {
            $whereConditions[] = "tr.recorded_at <= ?";
            $bindings[] = $request->end_date . ' 23:59:59';
        }

        if ($request->filled('employee_id')) {
            $whereConditions[] = "e.id = ?";
            $bindings[] = $request->employee_id;
        }

        if ($request->filled('manager_id')) {
            $whereConditions[] = "e.manager_id = ?";
            $bindings[] = $request->manager_id;
        }

        $whereClause = !empty($whereConditions)
            ? 'WHERE ' . implode(' AND ', $whereConditions)
            : '';

        $sql = "
            SELECT
                tr.id as registro_id,
                e.full_name as nome_funcionario,
                e.position as cargo,
                TIMESTAMPDIFF(YEAR, e.birth_date, CURDATE()) as idade,
                manager.name as nome_gestor,
                DATE_FORMAT(tr.recorded_at, '%d/%m/%Y %H:%i:%s') as data_hora_registro,
                tr.recorded_at as recorded_at_raw
            FROM time_records tr
            INNER JOIN employees e ON tr.employee_id = e.id
            INNER JOIN users u ON e.user_id = u.id
            LEFT JOIN users manager ON e.manager_id = manager.id
            {$whereClause}
            ORDER BY tr.recorded_at DESC
            LIMIT ? OFFSET ?
        ";

        $bindings[] = $perPage;
        $bindings[] = $offset;

        $records = DB::select($sql, $bindings);

        $countSql = "
            SELECT COUNT(*) as total
            FROM time_records tr
            INNER JOIN employees e ON tr.employee_id = e.id
            INNER JOIN users u ON e.user_id = u.id
            LEFT JOIN users manager ON e.manager_id = manager.id
            {$whereClause}
        ";

        $countBindings = array_slice($bindings, 0, -2);
        $totalRecords = DB::select($countSql, $countBindings)[0]->total;

        $lastPage = ceil($totalRecords / $perPage);

        $formattedRecords = array_map(function($record) {
            return [
                'id_registro' => $record->registro_id,
                'nome_funcionario' => $record->nome_funcionario,
                'cargo' => $record->cargo,
                'idade' => $record->idade,
                'nome_gestor' => $record->nome_gestor ?: 'Sem gestor',
                'data_hora_completa' => $record->data_hora_registro,
            ];
        }, $records);

        return response()->json([
            'success' => true,
            'message' => 'Relatório gerado com sucesso',
            'data' => $formattedRecords,
            'pagination' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $totalRecords,
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $totalRecords),
            ],
            'filters_applied' => [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'employee_id' => $request->employee_id,
                'manager_id' => $request->manager_id,
            ],
            ...config('app.debug') ? [
                'sql_info' => [
                    'note' => 'Este relatório foi gerado usando SQL puro conforme requisito do desafio',
                    'query_type' => 'Raw SQL with INNER/LEFT JOINs',
                    'performance' => 'Otimizado com índices em time_records(employee_id, recorded_at)',
                ]
            ] : []
        ]);
    }

    /**
     * Get summary statistics for reports dashboard.
     */
    public function summary(Request $request): JsonResponse
    {
        $statisticsQuery = "
            SELECT
                COUNT(tr.id) as total_registros,
                COUNT(DISTINCT tr.employee_id) as funcionarios_ativos,
                COUNT(DISTINCT e.manager_id) as gestores_ativos,
                DATE_FORMAT(MIN(tr.recorded_at), '%d/%m/%Y') as primeiro_registro,
                DATE_FORMAT(MAX(tr.recorded_at), '%d/%m/%Y') as ultimo_registro
            FROM time_records tr
            INNER JOIN employees e ON tr.employee_id = e.id
            INNER JOIN users u ON e.user_id = u.id
            WHERE u.is_active = 1
        ";

        $statistics = DB::select($statisticsQuery)[0];

        $monthlyQuery = "
            SELECT
                DATE_FORMAT(tr.recorded_at, '%Y-%m') as mes,
                DATE_FORMAT(tr.recorded_at, '%m/%Y') as mes_formatado,
                COUNT(tr.id) as total_registros,
                COUNT(DISTINCT tr.employee_id) as funcionarios_unicos
            FROM time_records tr
            INNER JOIN employees e ON tr.employee_id = e.id
            INNER JOIN users u ON e.user_id = u.id
            WHERE tr.recorded_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            AND u.is_active = 1
            GROUP BY DATE_FORMAT(tr.recorded_at, '%Y-%m')
            ORDER BY mes DESC
        ";

        $monthlyData = DB::select($monthlyQuery);

        $topEmployeesQuery = "
            SELECT
                e.full_name as nome_funcionario,
                e.position as cargo,
                COUNT(tr.id) as total_registros,
                manager.name as nome_gestor
            FROM time_records tr
            INNER JOIN employees e ON tr.employee_id = e.id
            INNER JOIN users u ON e.user_id = u.id
            LEFT JOIN users manager ON e.manager_id = manager.id
            WHERE MONTH(tr.recorded_at) = MONTH(CURDATE())
            AND YEAR(tr.recorded_at) = YEAR(CURDATE())
            AND u.is_active = 1
            GROUP BY e.id, e.full_name, e.position, manager.name
            ORDER BY total_registros DESC
            LIMIT 10
        ";

        $topEmployees = DB::select($topEmployeesQuery);

        return response()->json([
            'success' => true,
            'data' => [
                'overview' => [
                    'total_registros' => (int) $statistics->total_registros,
                    'funcionarios_ativos' => (int) $statistics->funcionarios_ativos,
                    'gestores_ativos' => (int) $statistics->gestores_ativos,
                    'primeiro_registro' => $statistics->primeiro_registro,
                    'ultimo_registro' => $statistics->ultimo_registro,
                ],
                'monthly_trend' => array_map(function($month) {
                    return [
                        'mes' => $month->mes_formatado,
                        'total_registros' => (int) $month->total_registros,
                        'funcionarios_unicos' => (int) $month->funcionarios_unicos,
                    ];
                }, $monthlyData),
                'top_employees_this_month' => array_map(function($employee) {
                    return [
                        'nome_funcionario' => $employee->nome_funcionario,
                        'cargo' => $employee->cargo,
                        'total_registros' => (int) $employee->total_registros,
                        'nome_gestor' => $employee->nome_gestor ?: 'Sem gestor',
                    ];
                }, $topEmployees),
            ],

            ...config('app.debug') ? [
                'sql_info' => [
                    'note' => 'Estatísticas geradas com SQL puro e funções de agregação',
                    'queries_used' => 3,
                    'performance' => 'Otimizado com índices compostos',
                ]
            ] : []
        ]);
    }

    /**
     * Export time records report data (for future CSV/Excel export).
     */
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d|after_or_equal:start_date',
            'employee_id' => 'nullable|integer|exists:employees,id',
            'manager_id' => 'nullable|integer|exists:users,id',
            'format' => 'nullable|in:json,csv',
        ]);

        $format = $request->get('format', 'json');

        $whereConditions = [
            "tr.recorded_at >= ?",
            "tr.recorded_at <= ?"
        ];
        $bindings = [
            $request->start_date . ' 00:00:00',
            $request->end_date . ' 23:59:59'
        ];

        if ($request->filled('employee_id')) {
            $whereConditions[] = "e.id = ?";
            $bindings[] = $request->employee_id;
        }

        if ($request->filled('manager_id')) {
            $whereConditions[] = "e.manager_id = ?";
            $bindings[] = $request->manager_id;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

        $sql = "
            SELECT
                tr.id as id_registro,
                e.full_name as nome_funcionario,
                e.position as cargo,
                TIMESTAMPDIFF(YEAR, e.birth_date, CURDATE()) as idade,
                manager.name as nome_gestor,
                DATE_FORMAT(tr.recorded_at, '%d/%m/%Y %H:%i:%s') as data_hora_completa,
                DATE_FORMAT(tr.recorded_at, '%d/%m/%Y') as data,
                DATE_FORMAT(tr.recorded_at, '%H:%i:%s') as hora
            FROM time_records tr
            INNER JOIN employees e ON tr.employee_id = e.id
            INNER JOIN users u ON e.user_id = u.id
            LEFT JOIN users manager ON e.manager_id = manager.id
            {$whereClause}
            ORDER BY tr.recorded_at DESC
        ";

        $records = DB::select($sql, $bindings);

        $exportData = array_map(function($record) {
            return [
                'ID do Registro' => $record->id_registro,
                'Nome do Funcionário' => $record->nome_funcionario,
                'Cargo' => $record->cargo,
                'Idade' => $record->idade,
                'Nome do Gestor' => $record->nome_gestor ?: 'Sem gestor',
                'Data e Hora Completa' => $record->data_hora_completa,
                'Data' => $record->data,
                'Hora' => $record->hora,
            ];
        }, $records);

        return response()->json([
            'success' => true,
            'message' => 'Dados exportados com sucesso',
            'data' => $exportData,
            'export_info' => [
                'total_records' => count($exportData),
                'period' => $request->start_date . ' a ' . $request->end_date,
                'format' => $format,
                'generated_at' => now()->format('d/m/Y H:i:s'),
            ],
            ...config('app.debug') ? [
                'sql_info' => [
                    'note' => 'Exportação gerada com SQL puro sem paginação',
                    'performance' => 'Recomendado limitar período para grandes volumes',
                ]
            ] : []
        ]);
    }
}
