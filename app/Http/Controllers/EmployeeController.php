<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Employee;
use App\Models\Address;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Services\CepService;

class EmployeeController extends Controller
{
    private CepService $cepService;

    public function __construct(CepService $cepService)
    {
        $this->cepService = $cepService;
    }

    /**
     * Display a listing of employees.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Employee::with(['user', 'address', 'manager'])
            ->orderBy('created_at', 'desc');

        if ($request->has('manager_id')) {
            $query->where('manager_id', $request->manager_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('email', 'like', "%{$search}%");
                  });
            });
        }

        $employees = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $employees->map(function ($employee) {
                return $this->formatEmployeeData($employee);
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
        DB::beginTransaction();

        try {
            $addressData = $this->cepService->getAddressByCep($request->cep);

            if (!$addressData) {
                return response()->json([
                    'success' => false,
                    'message' => 'CEP não encontrado. Verifique o CEP informado.'
                ], 400);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'employee',
                'is_active' => true,
            ]);

            $employee = Employee::create([
                'user_id' => $user->id,
                'full_name' => $request->full_name,
                'cpf' => $request->cpf,
                'position' => $request->position,
                'birth_date' => $request->birth_date,
                'manager_id' => $request->user()->id,
            ]);

            Address::create([
                'employee_id' => $employee->id,
                'cep' => $addressData['cep'],
                'street' => $addressData['street'],
                'number' => $request->number,
                'complement' => $request->complement,
                'neighborhood' => $addressData['neighborhood'],
                'city' => $addressData['city'],
                'state' => $addressData['state'],
            ]);

            DB::commit();

            $employee->load(['user', 'address', 'manager']);

            return response()->json([
                'success' => true,
                'message' => 'Funcionário cadastrado com sucesso',
                'data' => $this->formatEmployeeData($employee)
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

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
        $employee->load(['user', 'address', 'manager', 'timeRecords']);

        return response()->json([
            'success' => true,
            'data' => $this->formatEmployeeData($employee, true)
        ]);
    }

    /**
     * Update the specified employee.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        DB::beginTransaction();

        try {
            $userUpdates = [];
            if ($request->has('name')) $userUpdates['name'] = $request->name;
            if ($request->has('email')) $userUpdates['email'] = $request->email;
            if ($request->has('password')) $userUpdates['password'] = Hash::make($request->password);
            if ($request->has('is_active')) $userUpdates['is_active'] = $request->is_active;

            if (!empty($userUpdates)) {
                $employee->user->update($userUpdates);
            }

            $employeeUpdates = [];
            if ($request->has('full_name')) $employeeUpdates['full_name'] = $request->full_name;
            if ($request->has('cpf')) $employeeUpdates['cpf'] = $request->cpf;
            if ($request->has('position')) $employeeUpdates['position'] = $request->position;
            if ($request->has('birth_date')) $employeeUpdates['birth_date'] = $request->birth_date;

            if (!empty($employeeUpdates)) {
                $employee->update($employeeUpdates);
            }

            if ($request->has('cep')) {
                $addressData = $this->cepService->getAddressByCep($request->cep);

                if (!$addressData) {
                    return response()->json([
                        'success' => false,
                        'message' => 'CEP não encontrado. Verifique o CEP informado.'
                    ], 400);
                }

                $addressUpdates = [
                    'cep' => $addressData['cep'],
                    'street' => $addressData['street'],
                    'neighborhood' => $addressData['neighborhood'],
                    'city' => $addressData['city'],
                    'state' => $addressData['state'],
                ];

                if ($request->has('number')) $addressUpdates['number'] = $request->number;
                if ($request->has('complement')) $addressUpdates['complement'] = $request->complement;

                $employee->address->update($addressUpdates);
            }

            DB::commit();

            $employee->load(['user', 'address', 'manager']);

            return response()->json([
                'success' => true,
                'message' => 'Funcionário atualizado com sucesso',
                'data' => $this->formatEmployeeData($employee)
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar funcionário: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified employee.
     */
    public function destroy(Employee $employee): JsonResponse
    {
        try {
            $employee->user->delete();

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

    /**
     * Format employee data for API response.
     */
    private function formatEmployeeData(Employee $employee, bool $includeTimeRecords = false): array
    {
        $data = [
            'id' => $employee->id,
            'full_name' => $employee->full_name,
            'cpf' => $employee->formatted_cpf,
            'position' => $employee->position,
            'birth_date' => $employee->birth_date->format('d/m/Y'),
            'age' => $employee->age,
            'created_at' => $employee->created_at->format('d/m/Y H:i:s'),
            'user' => [
                'id' => $employee->user->id,
                'name' => $employee->user->name,
                'email' => $employee->user->email,
                'is_active' => $employee->user->is_active,
            ],
            'manager' => $employee->manager ? [
                'id' => $employee->manager->id,
                'name' => $employee->manager->name,
            ] : null,
            'address' => $employee->address ? [
                'cep' => $employee->address->formatted_cep,
                'full_address' => $employee->address->full_address,
                'street' => $employee->address->street,
                'number' => $employee->address->number,
                'complement' => $employee->address->complement,
                'neighborhood' => $employee->address->neighborhood,
                'city' => $employee->address->city,
                'state' => $employee->address->state,
            ] : null,
        ];

        if ($includeTimeRecords && $employee->relationLoaded('timeRecords')) {
            $data['time_records_count'] = $employee->timeRecords->count();
            $data['last_time_record'] = $employee->timeRecords->first()
                ? $employee->timeRecords->first()->formatted_recorded_at
                : null;
        }

        return $data;
    }
}
