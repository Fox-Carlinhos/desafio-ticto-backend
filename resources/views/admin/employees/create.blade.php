@extends('layouts.app')

@section('page-title', 'Adicionar Funcionário')

@section('content')
<div class="max-w-4xl mx-auto" x-data="createEmployee()">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900">Adicionar Funcionário</h1>
                <p class="mt-2 text-sm text-secondary-700">Cadastre um novo funcionário no sistema</p>
            </div>
            <a href="{{ route('employees.index') }}" class="btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar
            </a>
        </div>
    </div>

    <form @submit.prevent="handleSubmit">
        <div class="space-y-6">
            <!-- User Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-secondary-900">Informações de Acesso</h3>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Nome de Usuário</label>
                            <input type="text" x-model="form.name" class="form-input"
                                   placeholder="Nome para login" required>
                            <p x-show="errors.name" class="form-error" x-text="errors.name"></p>
                        </div>
                        <div>
                            <label class="form-label">Email</label>
                            <input type="email" x-model="form.email" class="form-input"
                                   placeholder="email@empresa.com" required>
                            <p x-show="errors.email" class="form-error" x-text="errors.email"></p>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Senha</label>
                        <input type="password" x-model="form.password" class="form-input"
                               placeholder="••••••••" required>
                        <p x-show="errors.password" class="form-error" x-text="errors.password"></p>
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-secondary-900">Informações Pessoais</h3>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Nome Completo</label>
                            <input type="text" x-model="form.full_name" class="form-input"
                                   placeholder="Nome completo do funcionário" required>
                            <p x-show="errors.full_name" class="form-error" x-text="errors.full_name"></p>
                        </div>
                        <div>
                            <label class="form-label">CPF</label>
                            <input type="text" x-model="form.cpf" class="form-input"
                                   placeholder="000.000.000-00" x-mask="999.999.999-99" required>
                            <p x-show="errors.cpf" class="form-error" x-text="errors.cpf"></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Cargo</label>
                            <input type="text" x-model="form.position" class="form-input"
                                   placeholder="Ex: Desenvolvedor, Analista..." required>
                            <p x-show="errors.position" class="form-error" x-text="errors.position"></p>
                        </div>
                        <div>
                            <label class="form-label">Data de Nascimento</label>
                            <input type="date" x-model="form.birth_date" class="form-input" required>
                            <p x-show="errors.birth_date" class="form-error" x-text="errors.birth_date"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-secondary-900">Endereço</h3>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label">CEP</label>
                            <input type="text" x-model="form.cep" class="form-input"
                                   placeholder="00000-000" x-mask="99999-999" required
                                   @blur="fetchAddress()">
                            <p x-show="errors.cep" class="form-error" x-text="errors.cep"></p>
                        </div>
                        <div>
                            <label class="form-label">Número</label>
                            <input type="text" x-model="form.number" class="form-input"
                                   placeholder="123" required>
                            <p x-show="errors.number" class="form-error" x-text="errors.number"></p>
                        </div>
                        <div>
                            <label class="form-label">Complemento</label>
                            <input type="text" x-model="form.complement" class="form-input"
                                   placeholder="Apto 101, Bloco A...">
                            <p x-show="errors.complement" class="form-error" x-text="errors.complement"></p>
                        </div>
                    </div>

                    <!-- Address will be auto-filled -->
                    <div x-show="addressLoading" class="text-sm text-secondary-500">
                        Buscando endereço...
                    </div>
                    <div x-show="addressInfo" class="text-sm text-secondary-600 bg-secondary-50 p-3 rounded-lg">
                        <span x-text="addressInfo"></span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('employees.index') }}" class="btn-secondary">
                    Cancelar
                </a>
                <button type="submit" :disabled="submitting" class="btn-primary">
                    Salvar
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('createEmployee', () => ({
        form: {
            name: '',
            email: '',
            password: '',
            full_name: '',
            cpf: '',
            position: '',
            birth_date: '',
            cep: '',
            number: '',
            complement: ''
        },
        errors: {},
        submitting: false,
        addressLoading: false,
        addressInfo: '',

        async fetchAddress() {
            if (!this.form.cep || this.form.cep.length < 8) return;

            this.addressLoading = true;
            this.addressInfo = '';

            try {
                const cep = this.form.cep.replace(/\D/g, '');
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await response.json();

                if (!data.erro) {
                    this.addressInfo = `${data.logradouro}, ${data.bairro}, ${data.localidade} - ${data.uf}`;
                } else {
                    this.addressInfo = 'CEP não encontrado';
                }
            } catch (error) {
                console.error('Erro ao buscar CEP:', error);
                this.addressInfo = 'Erro ao buscar CEP';
            } finally {
                this.addressLoading = false;
            }
        },

        async handleSubmit() {
            console.log('=== HANDLE SUBMIT CALLED ===');
            this.submitting = true;
            this.errors = {};

            try {
                console.log('Creating employee with data:', this.form);

                const result = await window.apiRequest('/employees', 'POST', this.form);
                console.log('Create result:', result);

                if (result.success) {
                    alert('Funcionário criado com sucesso!');
                    window.location.href = '/admin/employees';
                } else {
                    if (result.errors) {
                        this.errors = result.errors;
                    } else {
                        throw new Error(result.message || 'Erro desconhecido');
                    }
                }
            } catch (error) {
                console.error('Error creating employee:', error);

                if (error.status === 422 && error.errors) {
                    this.errors = error.errors;
                } else {
                    alert('Erro ao criar funcionário: ' + error.message);
                }
            } finally {
                this.submitting = false;
            }
        }
    }));
});
</script>
@endpush
@endsection
