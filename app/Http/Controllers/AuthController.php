<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Login user and create token
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados de validação inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            Log::warning('Failed authentication attempt', [
                'email' => $request->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Email ou senha inválidos'
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário inativo'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        Log::info('Successful authentication', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'is_active' => $user->is_active,
                ]
            ]
        ]);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request): JsonResponse
    {
        $currentToken = $request->user()->currentAccessToken();

        if ($currentToken) {
            $currentToken->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso'
        ]);
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

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

        return response()->json([
            'success' => true,
            'data' => $userData
        ]);
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados de validação inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Senha atual incorreta'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Senha alterada com sucesso'
        ]);
    }

    /**
     * Get user profile data
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

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

        return response()->json([
            'success' => true,
            'data' => $responseData
        ]);
    }


}
