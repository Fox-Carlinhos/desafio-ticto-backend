<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Employee;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $employeeId = $this->route('employee');

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->getEmployeeUserId($employeeId))
            ],
            'password' => 'sometimes|string|min:6',

            'full_name' => 'sometimes|required|string|max:255',
            'cpf' => [
                'sometimes',
                'required',
                'string',
                'size:11',
                Rule::unique('employees', 'cpf')->ignore($employeeId),
                function ($attribute, $value, $fail) {
                    if (!Employee::isValidCpf($value)) {
                        $fail('O CPF informado é inválido.');
                    }
                },
            ],
            'position' => 'sometimes|required|string|max:255',
            'birth_date' => 'sometimes|required|date|before:today',

            'cep' => 'sometimes|required|string|size:8',
            'number' => 'nullable|string|max:10',
            'complement' => 'nullable|string|max:255',

            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ter um formato válido.',
            'email.unique' => 'Este email já está em uso.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',

            'full_name.required' => 'O nome completo é obrigatório.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.size' => 'O CPF deve ter exatamente 11 dígitos.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'position.required' => 'O cargo é obrigatório.',
            'birth_date.required' => 'A data de nascimento é obrigatória.',
            'birth_date.date' => 'A data de nascimento deve ser uma data válida.',
            'birth_date.before' => 'A data de nascimento deve ser anterior a hoje.',

            'cep.required' => 'O CEP é obrigatório.',
            'cep.size' => 'O CEP deve ter exatamente 8 dígitos.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'cpf' => isset($this->cpf) ? preg_replace('/[^0-9]/', '', $this->cpf) : null,
            'cep' => isset($this->cep) ? preg_replace('/[^0-9]/', '', $this->cep) : null,
        ]);
    }

    /**
     * Get the user ID for the employee being updated.
     */
    private function getEmployeeUserId($employeeId): ?int
    {
        $employee = Employee::find($employeeId);
        return $employee ? $employee->user_id : null;
    }
}
