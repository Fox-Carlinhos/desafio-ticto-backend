<?php

namespace App\Services;

use App\Models\User;
use App\Models\Employee;
use App\Models\Address;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EmployeeService
{
    public function __construct(
        private CepService $cepService
    ) {}

    /**
     * Get paginated list of employees with filters.
     */
    public function getEmployeesList(Request $request): LengthAwarePaginator
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

        return $query->paginate(15);
    }

    /**
     * Create a new employee with user and address.
     */
    public function createEmployee(StoreEmployeeRequest $request): Employee
    {
        DB::beginTransaction();

        try {
            $addressData = $this->cepService->getAddressByCep($request->cep);

            if (!$addressData) {
                throw new \Exception('CEP não encontrado. Verifique o CEP informado.');
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

            $this->logEmployeeCreation($request, $employee, $user);

            $employee->load(['user', 'address', 'manager']);

            return $employee;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Update an existing employee.
     */
    public function updateEmployee(UpdateEmployeeRequest $request, Employee $employee): Employee
    {
        DB::beginTransaction();

        try {
            $this->updateUserData($request, $employee->user);

            $this->updateEmployeeData($request, $employee);

            if ($request->has('cep')) {
                $this->updateEmployeeAddress($request, $employee);
            }

            DB::commit();

            $employee->load(['user', 'address', 'manager']);

            return $employee;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Delete an employee and associated user.
     */
    public function deleteEmployee(Employee $employee): bool
    {
        try {
            return $employee->user->delete();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get single employee with relationships.
     */
    public function getEmployeeById(Employee $employee): Employee
    {
        $employee->load(['user', 'address', 'manager', 'timeRecords']);
        return $employee;
    }

    /**
     * Format employee data for API response.
     */
    public function formatEmployeeData(Employee $employee, bool $includeTimeRecords = false): array
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

    /**
     * Update user data if provided in request.
     */
    private function updateUserData(UpdateEmployeeRequest $request, User $user): void
    {
        $userUpdates = [];

        if ($request->has('name')) $userUpdates['name'] = $request->name;
        if ($request->has('email')) $userUpdates['email'] = $request->email;
        if ($request->has('password')) $userUpdates['password'] = Hash::make($request->password);
        if ($request->has('is_active')) $userUpdates['is_active'] = $request->is_active;

        if (!empty($userUpdates)) {
            $user->update($userUpdates);
        }
    }

    /**
     * Update employee data if provided in request.
     */
    private function updateEmployeeData(UpdateEmployeeRequest $request, Employee $employee): void
    {
        $employeeUpdates = [];

        if ($request->has('full_name')) $employeeUpdates['full_name'] = $request->full_name;
        if ($request->has('cpf')) $employeeUpdates['cpf'] = $request->cpf;
        if ($request->has('position')) $employeeUpdates['position'] = $request->position;
        if ($request->has('birth_date')) $employeeUpdates['birth_date'] = $request->birth_date;

        if (!empty($employeeUpdates)) {
            $employee->update($employeeUpdates);
        }
    }

    /**
     * Update employee address with new CEP data.
     */
    private function updateEmployeeAddress(UpdateEmployeeRequest $request, Employee $employee): void
    {
        $addressData = $this->cepService->getAddressByCep($request->cep);

        if (!$addressData) {
            throw new \Exception('CEP não encontrado. Verifique o CEP informado.');
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

    /**
     * Log employee creation for audit purposes.
     */
    private function logEmployeeCreation(StoreEmployeeRequest $request, Employee $employee, User $user): void
    {
        Log::info('Employee created by admin', [
            'admin_id' => $request->user()->id,
            'admin_email' => $request->user()->email,
            'employee_id' => $employee->id,
            'employee_name' => $employee->full_name,
            'employee_email' => $user->email,
            'employee_cpf' => $employee->cpf,
            'timestamp' => now()->toISOString(),
        ]);
    }
}
