@extends('layouts.app')

@section('page-title', 'Detalhes do Funcionário')

@section('content')
<div class="max-w-4xl mx-auto" x-data="showEmployee({{ $employee->id }})">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900">Detalhes do Funcionário</h1>
                <p class="mt-2 text-sm text-secondary-700">Visualizar informações completas</p>
            </div>
            <div class="space-x-3">
                <a :href="`/admin/employees/${employee.id}/edit`" class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Editar
                </a>
                <a href="{{ route('employees.index') }}" class="btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar
                </a>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="space-y-6">
        <div class="card">
            <div class="card-body">
                <div class="animate-pulse space-y-4">
                    <div class="skeleton h-6 w-1/4"></div>
                    <div class="skeleton h-4 w-1/2"></div>
                    <div class="skeleton h-4 w-1/3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Details -->
    <div x-show="!loading && employee" class="space-y-6">
        <!-- Header with Avatar -->
        <div class="card">
            <div class="card-body">
                <div class="flex items-center space-x-6">
                    <div class="flex-shrink-0">
                        <div class="w-20 h-20 rounded-full bg-primary-100 flex items-center justify-center">
                            <span class="text-2xl font-bold text-primary-700" x-text="employee.full_name?.charAt(0) || 'U'"></span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-secondary-900" x-text="employee.full_name"></h2>
                        <p class="text-lg text-secondary-600" x-text="employee.position"></p>
                        <div class="mt-2 flex items-center space-x-4">
                            <span :class="employee.user?.is_active ? 'badge-success' : 'badge-danger'" class="badge">
                                <span x-text="employee.user?.is_active ? 'Ativo' : 'Inativo'"></span>
                            </span>
                            <span class="text-sm text-secondary-500">
                                ID: <span x-text="employee.id"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-secondary-900">Informações Pessoais</h3>
            </div>
            <div class="card-body">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-secondary-500">Nome Completo</dt>
                        <dd class="mt-1 text-sm text-secondary-900" x-text="employee.full_name"></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-secondary-500">CPF</dt>
                        <dd class="mt-1 text-sm text-secondary-900" x-text="employee.cpf"></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-secondary-500">Data de Nascimento</dt>
                        <dd class="mt-1 text-sm text-secondary-900" x-text="formatDate(employee.birth_date)"></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-secondary-500">Idade</dt>
                        <dd class="mt-1 text-sm text-secondary-900">
                            <span x-text="employee.age"></span> anos
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-secondary-500">Cargo</dt>
                        <dd class="mt-1 text-sm text-secondary-900" x-text="employee.position"></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-secondary-500">Cadastrado em</dt>
                        <dd class="mt-1 text-sm text-secondary-900" x-text="formatDate(employee.created_at)"></dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-secondary-900">Informações de Contato</h3>
            </div>
            <div class="card-body">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-secondary-500">Email</dt>
                        <dd class="mt-1 text-sm text-secondary-900" x-text="employee.user?.email"></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-secondary-500">Nome de Usuário</dt>
                        <dd class="mt-1 text-sm text-secondary-900" x-text="employee.user?.name"></dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Address Information -->
        <div x-show="employee.address" class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-secondary-900">Endereço</h3>
            </div>
            <div class="card-body">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-secondary-500">CEP</dt>
                        <dd class="mt-1 text-sm text-secondary-900" x-text="employee.address?.formatted_cep"></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-secondary-500">Número</dt>
                        <dd class="mt-1 text-sm text-secondary-900" x-text="employee.address?.number"></dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-secondary-500">Endereço Completo</dt>
                        <dd class="mt-1 text-sm text-secondary-900" x-text="employee.address?.full_address"></dd>
                    </div>
                    <div x-show="employee.address?.complement">
                        <dt class="text-sm font-medium text-secondary-500">Complemento</dt>
                        <dd class="mt-1 text-sm text-secondary-900" x-text="employee.address?.complement"></dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Manager Information -->
        <div x-show="employee.manager" class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-secondary-900">Gestor</h3>
            </div>
            <div class="card-body">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-full bg-secondary-100 flex items-center justify-center">
                        <span class="text-sm font-medium text-secondary-700" x-text="employee.manager?.full_name?.charAt(0) || 'G'"></span>
                    </div>
                    <div>
                        <p class="font-medium text-secondary-900" x-text="employee.manager?.full_name"></p>
                        <p class="text-sm text-secondary-500" x-text="employee.manager?.position"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Error State -->
    <div x-show="!loading && !employee" class="card">
        <div class="card-body text-center py-12">
            <svg class="mx-auto h-12 w-12 text-danger-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-secondary-900">Funcionário não encontrado</h3>
            <p class="mt-2 text-sm text-secondary-500">O funcionário solicitado não existe ou foi removido</p>
            <div class="mt-6">
                <a href="{{ route('employees.index') }}" class="btn-primary">
                    Voltar para Lista
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('showEmployee', (employeeId) => ({
        employee: null,
        loading: true,

        async init() {
            await this.loadEmployee();
        },

        async loadEmployee() {
            this.loading = true;
            try {
                const result = await window.apiRequest(`/employees/${employeeId}`);
                this.employee = result.data;
            } catch (error) {
                console.error('Erro ao carregar funcionário:', error);
                this.employee = null;
            } finally {
                this.loading = false;
            }
        },

        formatDate(date) {
            if (!date) return 'N/A';
            return new Date(date).toLocaleDateString('pt-BR');
        }
    }));
});
</script>
@endsection
