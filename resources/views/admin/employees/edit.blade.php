@extends('layouts.app')

@section('page-title', 'Editar Funcionário')

@section('content')
<div class="max-w-4xl mx-auto" x-data="employeeEdit()" x-init="init()">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900">Editar Funcionário</h1>
                <p class="mt-2 text-sm text-secondary-700">Atualize as informações do funcionário no sistema</p>
            </div>
            <a href="{{ route('employees.index') }}" class="btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar
            </a>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="card">
        <div class="card-body">
            <div class="animate-pulse space-y-6">
                <div class="skeleton h-4 w-1/4"></div>
                <div class="skeleton h-10 w-full"></div>
                <div class="skeleton h-10 w-full"></div>
                <div class="skeleton h-10 w-2/3"></div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div x-show="!loading" class="space-y-6">
        <!-- Personal Information -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold text-secondary-900">Informações Pessoais</h2>
            </div>
            <div class="card-body space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="form-label required">Nome Completo</label>
                        <input type="text" x-model="form.full_name" class="form-input"
                               placeholder="Nome completo do funcionário">
                        <div x-show="errors.full_name" class="form-error" x-text="errors.full_name"></div>
                    </div>

                    <div>
                        <label class="form-label required">CPF</label>
                        <input type="text" x-model="form.cpf" class="form-input"
                               placeholder="000.000.000-00" maxlength="14"
                               @input="formatCpf($event.target.value)">
                        <div x-show="errors.cpf" class="form-error" x-text="errors.cpf"></div>
                    </div>

                    <div>
                        <label class="form-label required">Cargo/Posição</label>
                        <input type="text" x-model="form.position" class="form-input"
                               placeholder="Ex: Desenvolvedor Full Stack">
                        <div x-show="errors.position" class="form-error" x-text="errors.position"></div>
                    </div>

                    <div>
                        <label class="form-label required">Data de Nascimento</label>
                        <input type="date" x-model="form.birth_date" class="form-input">
                        <div x-show="errors.birth_date" class="form-error" x-text="errors.birth_date"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Information -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold text-secondary-900">Informações de Acesso</h2>
            </div>
            <div class="card-body space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="form-label required">Nome de Usuário</label>
                        <input type="text" x-model="form.name" class="form-input"
                               placeholder="Nome para login">
                        <div x-show="errors.name" class="form-error" x-text="errors.name"></div>
                    </div>

                    <div>
                        <label class="form-label required">Email</label>
                        <input type="email" x-model="form.email" class="form-input"
                               placeholder="email@exemplo.com">
                        <div x-show="errors.email" class="form-error" x-text="errors.email"></div>
                    </div>

                    <div>
                        <label class="form-label">Status</label>
                        <select x-model="form.is_active" class="form-input">
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold text-secondary-900">Endereço</h2>
            </div>
            <div class="card-body space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="form-label required">CEP</label>
                                                <input type="text" x-model="form.cep" class="form-input"
                               placeholder="00000-000" maxlength="9"
                               @input="formatCep($event.target.value)"
                               @blur="lookupAddress()"
                               @keyup.enter="lookupAddress()">
                        <div x-show="errors.cep" class="form-error" x-text="errors.cep"></div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label">Logradouro</label>
                        <input type="text" x-model="form.street" class="form-input"
                               placeholder="Rua, Avenida, etc." readonly>
                    </div>

                    <div>
                        <label class="form-label required">Número</label>
                        <input type="text" x-model="form.number" class="form-input"
                               placeholder="123">
                        <div x-show="errors.number" class="form-error" x-text="errors.number"></div>
                    </div>

                    <div>
                        <label class="form-label">Complemento</label>
                        <input type="text" x-model="form.complement" class="form-input"
                               placeholder="Apto, Sala, etc.">
                    </div>

                    <div>
                        <label class="form-label">Bairro</label>
                        <input type="text" x-model="form.neighborhood" class="form-input" readonly>
                    </div>

                    <div>
                        <label class="form-label">Cidade</label>
                        <input type="text" x-model="form.city" class="form-input" readonly>
                    </div>

                    <div>
                        <label class="form-label">Estado</label>
                        <input type="text" x-model="form.state" class="form-input" readonly>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('employees.index') }}" class="btn-secondary">
                Cancelar
            </a>
            <button @click="handleSubmit()" class="btn-primary" :disabled="submitting">
                <span x-show="!submitting">Atualizar Funcionário</span>
                <span x-show="submitting" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Atualizando...
                </span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('employeeEdit', () => ({
        loading: true,
        submitting: false,
        employeeId: null,
        form: {
            full_name: '',
            cpf: '',
            position: '',
            birth_date: '',
            name: '',
            email: '',
            is_active: '1',
            cep: '',
            street: '',
            number: '',
            complement: '',
            neighborhood: '',
            city: '',
            state: ''
        },
        errors: {},

        async init() {
            const pathParts = window.location.pathname.split('/');
            this.employeeId = pathParts[pathParts.length - 2];

            await this.loadEmployee();
        },

        async loadEmployee() {
            this.loading = true;
            try {
                const result = await window.apiRequest(`/employees/${this.employeeId}`, 'GET');

                if (result.success && result.data) {
                    const employee = result.data;

                    this.form = {
                        full_name: employee.full_name || '',
                        cpf: employee.cpf || '',
                        position: employee.position || '',
                        birth_date: this.convertDateToISO(employee.birth_date) || '',
                        name: employee.user?.name || '',
                        email: employee.user?.email || '',
                        is_active: employee.user?.is_active ? '1' : '0',
                        cep: employee.address?.cep || '',
                        street: employee.address?.street || '',
                        number: employee.address?.number || '',
                        complement: employee.address?.complement || '',
                        neighborhood: employee.address?.neighborhood || '',
                        city: employee.address?.city || '',
                        state: employee.address?.state || ''
                    };
                }
            } catch (error) {
                console.error('Error loading employee:', error);
                alert('Erro ao carregar dados do funcionário: ' + error.message);
            } finally {
                this.loading = false;
            }
        },

        async handleSubmit() {
            this.submitting = true;
            this.errors = {};

            try {
                const result = await window.apiRequest(`/employees/${this.employeeId}`, 'PUT', this.form);

                if (result.success) {
                    alert('Funcionário atualizado com sucesso!');
                    window.location.href = '/admin/employees';
                } else {
                    if (result.errors) {
                        this.errors = result.errors;
                    } else {
                        throw new Error(result.message || 'Erro desconhecido');
                    }
                }
            } catch (error) {
                console.error('Error updating employee:', error);

                if (error.status === 422 && error.errors) {
                    this.errors = error.errors;
                } else {
                    alert('Erro ao atualizar funcionário: ' + error.message);
                }
            } finally {
                this.submitting = false;
            }
        },

        formatCpf(value) {
            let cpf = value.replace(/\D/g, '');

            if (cpf.length >= 11) {
                cpf = cpf.substring(0, 11);
                cpf = cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
            } else if (cpf.length >= 9) {
                cpf = cpf.replace(/(\d{3})(\d{3})(\d{3})/, '$1.$2.$3');
            } else if (cpf.length >= 6) {
                cpf = cpf.replace(/(\d{3})(\d{3})/, '$1.$2');
            } else if (cpf.length >= 3) {
                cpf = cpf.replace(/(\d{3})/, '$1');
            }

            this.form.cpf = cpf;
        },

        formatCep(value) {
            let cep = value.replace(/\D/g, '');

            if (cep.length >= 8) {
                cep = cep.substring(0, 8);
                cep = cep.replace(/(\d{5})(\d{3})/, '$1-$2');
            }

            this.form.cep = cep;
        },

                convertDateToISO(dateString) {
            if (!dateString) return '';

            const parts = dateString.split('/');
            if (parts.length === 3) {
                const [day, month, year] = parts;
                return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
            }

            return dateString;
        },

                async lookupAddress() {
            if (this.form.cep.length === 9) {
                try {
                    const cleanCep = this.form.cep.replace(/\D/g, '');
                    const response = await fetch(`https://viacep.com.br/ws/${cleanCep}/json/`);
                    const data = await response.json();

                    if (!data.erro) {
                        this.form.street = data.logradouro || '';
                        this.form.neighborhood = data.bairro || '';
                        this.form.city = data.localidade || '';
                        this.form.state = data.uf || '';
                    } else {
                        console.warn('CEP não encontrado');
                    }
                } catch (error) {
                    console.error('Error looking up CEP:', error);
                }
            }
        }
    }));
});
</script>
@endpush
@endsection
