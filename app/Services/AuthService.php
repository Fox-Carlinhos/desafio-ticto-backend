<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService
{
    /**
     * Authenticate user and create token.
     */
    public function authenticateUser(string $email, string $password, Request $request): array
    {
        $credentials = ['email' => $email, 'password' => $password];

        if (!Auth::attempt($credentials)) {
            $this->logFailedAuthentication($email, $request);
            throw new \Exception('Email ou senha inválidos');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->is_active) {
            throw new \Exception('Usuário inativo');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $this->logSuccessfulAuthentication($user, $request);

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
            ]
        ];
    }

    /**
     * Logout user by revoking current token.
     */
    public function logoutUser(Request $request): bool
    {
        $currentToken = $request->user()->currentAccessToken();

        if ($currentToken) {
            $currentToken->delete();
            return true;
        }

        return false;
    }

    /**
     * Get current authenticated user data.
     */
    public function getCurrentUserData(User $user): array
    {
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => $user->is_active,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];

        if ($user->isEmployee() && $user->employee) {
            $userData['employee'] = [
                'id' => $user->employee->id,
                'full_name' => $user->employee->full_name,
                'cpf' => $user->employee->formatted_cpf,
                'position' => $user->employee->position,
                'age' => $user->employee->age,
            ];
        } elseif ($user->isEmployee() && !$user->employee) {
            $userData['employee'] = null;
        }

        if ($user->isAdmin()) {
            $userData['managed_employees_count'] = Employee::where('manager_id', $user->id)->count();
        }

        return $userData;
    }

    /**
     * Change user password.
     */
    public function changeUserPassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new \Exception('Senha atual incorreta');
        }

        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        return true;
    }

    /**
     * Get user profile data (works for both admin and employee).
     */
    public function getUserProfile(User $user): array
    {
        $responseData = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ];

        if ($user->isEmployee() && $user->employee) {
            $employee = $user->employee;
            $employee->load(['manager', 'address']);

            $responseData['employee'] = [
                'id' => $employee->id,
                'full_name' => $employee->full_name,
                'cpf' => $employee->formatted_cpf,
                'position' => $employee->position,
                'birth_date' => $employee->birth_date,
                'age' => $employee->age,
                'manager' => $employee->manager ? [
                    'id' => $employee->manager->id,
                    'name' => $employee->manager->name
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
                ] : null
            ];
        } elseif ($user->isAdmin()) {
            $responseData['admin'] = [
                'role' => 'admin',
                'managed_employees_count' => Employee::where('manager_id', $user->id)->count(),
            ];
        }

        return $responseData;
    }

    /**
     * Log failed authentication attempt for security audit.
     */
    private function logFailedAuthentication(string $email, Request $request): void
    {
        Log::warning('Failed authentication attempt', [
            'email' => $email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log successful authentication for security audit.
     */
    private function logSuccessfulAuthentication(User $user, Request $request): void
    {
        Log::info('Successful authentication', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
