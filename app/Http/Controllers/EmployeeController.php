<?php

namespace App\Http\Controllers;

use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Employee;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;

class EmployeeController extends Controller
{
    public function __construct(
        private EmployeeService $employeeService
    ) {}

    /**
     * Display a listing of employees.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $employees */
        $employees = $this->employeeService->getEmployeesList($request);

        return response()->json([
            'success' => true,
            'data' => collect($employees->items())->map(function ($employee) {
                return $this->employeeService->formatEmployeeData($employee);
            }),
            'pagination' => [
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
            ]
        ]);
    }

    /**
     * Store a newly created employee.
     */
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        try {
            $employee = $this->employeeService->createEmployee($request);

            return response()->json([
                'success' => true,
                'message' => 'Funcionário cadastrado com sucesso',
                'data' => $this->employeeService->formatEmployeeData($employee)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cadastrar funcionário: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee): JsonResponse
    {
        $employee = $this->employeeService->getEmployeeById($employee);

        return response()->json([
            'success' => true,
            'data' => $this->employeeService->formatEmployeeData($employee, true)
        ]);
    }

    /**
     * Update the specified employee.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        try {
            $employee = $this->employeeService->updateEmployee($request, $employee);

            return response()->json([
                'success' => true,
                'message' => 'Funcionário atualizado com sucesso',
                'data' => $this->employeeService->formatEmployeeData($employee)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar funcionário: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified employee
     */
    public function destroy(Employee $employee): JsonResponse
    {
        try {
            $this->employeeService->deleteEmployee($employee);

            return response()->json([
                'success' => true,
                'message' => 'Funcionário removido com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover funcionário: ' . $e->getMessage()
            ], 500);
        }
    }
}
