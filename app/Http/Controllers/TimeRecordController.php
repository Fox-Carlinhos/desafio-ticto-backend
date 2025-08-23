<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\TimeRecord;
use App\Models\Employee;
use Carbon\Carbon;

class TimeRecordController extends Controller
{
    /**
     * Record time punch for authenticated employee
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->employee) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não possui perfil de funcionário'
            ], 400);
        }

        try {
            $timeRecord = TimeRecord::create([
                'employee_id' => $user->employee->id,
                'recorded_at' => now(),
            ]);

            Log::info('Time record created', [
                'time_record_id' => $timeRecord->id,
                'employee_id' => $user->employee->id,
                'employee_name' => $user->employee->full_name,
                'employee_position' => $user->employee->position,
                'manager_id' => $user->employee->manager_id,
                'recorded_at' => $timeRecord->recorded_at->toISOString(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ponto registrado com sucesso',
                'data' => [
                    'id' => $timeRecord->id,
                    'recorded_at' => $timeRecord->formatted_recorded_at,
                    'employee' => [
                        'id' => $user->employee->id,
                        'full_name' => $user->employee->full_name,
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar ponto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get time records for authenticated employee
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->employee) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não possui perfil de funcionário'
            ], 400);
        }

        $query = TimeRecord::where('employee_id', $user->employee->id)
            ->orderBy('recorded_at', 'desc');

        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();

            $query->betweenDates($startDate, $endDate);
        }

        if ($request->has('date')) {
            $date = Carbon::createFromFormat('Y-m-d', $request->date);
            $query->byDate($date);
        }

        $timeRecords = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $timeRecords->map(function ($record) {
                return [
                    'id' => $record->id,
                    'recorded_at' => $record->formatted_recorded_at,
                    'date' => $record->recorded_at->format('d/m/Y'),
                    'time' => $record->recorded_at->format('H:i:s'),
                ];
            }),
            'pagination' => [
                'current_page' => $timeRecords->currentPage(),
                'last_page' => $timeRecords->lastPage(),
                'per_page' => $timeRecords->perPage(),
                'total' => $timeRecords->total(),
            ]
        ]);
    }

    /**
     * Get time records summary for authenticated employee
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->employee) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não possui perfil de funcionário'
            ], 400);
        }

        $employee = $user->employee;

        $todayRecords = TimeRecord::where('employee_id', $employee->id)
            ->byDate(today())
            ->orderBy('recorded_at', 'asc')
            ->get();

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        $monthRecordsCount = TimeRecord::where('employee_id', $employee->id)
            ->betweenDates($monthStart, $monthEnd)
            ->count();

        $weekStart = Carbon::now()->subDays(6)->startOfDay();
        $weekEnd = Carbon::now()->endOfDay();
        $weekRecords = TimeRecord::where('employee_id', $employee->id)
            ->betweenDates($weekStart, $weekEnd)
            ->orderBy('recorded_at', 'desc')
            ->get()
            ->groupBy(function ($record) {
                return $record->recorded_at->format('Y-m-d');
            });

        return response()->json([
            'success' => true,
            'data' => [
                'today' => [
                    'date' => today()->format('d/m/Y'),
                    'records' => $todayRecords->map(function ($record) {
                        return [
                            'id' => $record->id,
                            'time' => $record->recorded_at->format('H:i:s'),
                        ];
                    }),
                    'count' => $todayRecords->count(),
                ],
                'this_month' => [
                    'period' => $monthStart->format('d/m/Y') . ' a ' . $monthEnd->format('d/m/Y'),
                    'total_records' => $monthRecordsCount,
                ],
                'last_7_days' => [
                    'period' => $weekStart->format('d/m/Y') . ' a ' . $weekEnd->format('d/m/Y'),
                    'daily_records' => $weekRecords->map(function ($dayRecords, $date) {
                        return [
                            'date' => Carbon::createFromFormat('Y-m-d', $date)->format('d/m/Y'),
                            'count' => $dayRecords->count(),
                            'times' => $dayRecords->map(function ($record) {
                                return $record->recorded_at->format('H:i:s');
                            })->toArray(),
                        ];
                    })->values(),
                ],
            ]
        ]);
    }

    /**
     * Get today's status for quick check
     */
    public function todayStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->employee) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não possui perfil de funcionário'
            ], 400);
        }

        $todayRecords = TimeRecord::where('employee_id', $user->employee->id)
            ->byDate(today())
            ->orderBy('recorded_at', 'asc')
            ->get();

        $lastRecord = $todayRecords->last();

        return response()->json([
            'success' => true,
            'data' => [
                'date' => today()->format('d/m/Y'),
                'records_count' => $todayRecords->count(),
                'last_record' => $lastRecord ? [
                    'time' => $lastRecord->recorded_at->format('H:i:s'),
                    'formatted' => $lastRecord->formatted_recorded_at,
                ] : null,
                'can_record' => true,
            ]
        ]);
    }
}
