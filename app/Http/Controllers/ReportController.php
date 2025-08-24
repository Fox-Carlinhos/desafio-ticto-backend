<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    public function timeRecordsReport(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
            'employee_id' => 'nullable|integer|exists:employees,id',
            'manager_id' => 'nullable|integer|exists:users,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $reportData = $this->reportService->generateTimeRecordsReport($request);

        return response()->json([
            'success' => true,
            'message' => 'Relatório gerado com sucesso',
            'data' => $reportData['records'],
            'pagination' => $reportData['pagination'],
            'filters_applied' => $reportData['filters_applied'],
            ...($reportData['sql_info'] ? ['sql_info' => $reportData['sql_info']] : [])
        ]);
    }

    /**
     * Get summary statistics for reports dashboard
     */
    public function summary(Request $request): JsonResponse
    {
        $summaryData = $this->reportService->generateSummary();

        return response()->json([
            'success' => true,
            'data' => $summaryData,
            ...($summaryData['sql_info'] ? ['sql_info' => $summaryData['sql_info']] : [])
        ]);
    }

    /**
     * Export time records report data
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

        $exportData = $this->reportService->exportData($request);

        return response()->json([
            'success' => true,
            'message' => 'Dados exportados com sucesso',
            'data' => $exportData['data'],
            'export_info' => $exportData['export_info'],
            ...($exportData['sql_info'] ? ['sql_info' => $exportData['sql_info']] : [])
        ]);
    }
}
