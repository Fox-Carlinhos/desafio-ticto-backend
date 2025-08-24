@extends('layouts.app')

@section('page-title', 'Gerenciar Funcionários')

@section('content')
<div x-data="employeeManager()" x-init="init()">
    <!-- Header with Actions -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Funcionários</h1>
            <p class="mt-2 text-sm text-secondary-700">Gerencie todos os funcionários da empresa</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('employees.create') }}" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Adicionar Funcionário
            </a>
        </div>
    </div>

    <!-- Employees List -->
    <div class="card">
        <div class="overflow-hidden">
            <!-- Loading State -->
            <div x-show="loading" class="p-6">
                <div class="animate-pulse space-y-4">
                    <div class="skeleton h-4 w-1/4"></div>
                    <div class="skeleton h-12 w-full"></div>
                    <div class="skeleton h-12 w-full"></div>
                    <div class="skeleton h-12 w-full"></div>
                </div>
                <p class="mt-4 text-center text-sm text-secondary-600">Carregando funcionários...</p>
            </div>

            <!-- Table -->
            <div x-show="!loading && employees.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-secondary-200">
                    <thead class="bg-secondary-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider w-2/5">Funcionário</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider w-1/5">Posição</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider w-1/5">Gestor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider w-1/12">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider w-1/12">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-secondary-200">
                        <template x-for="employee in employees" :key="employee.id">
                            <tr class="hover:bg-secondary-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                                                <span class="text-sm font-medium text-primary-700" x-text="employee.full_name?.charAt(0) || 'U'"></span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-secondary-900" x-text="employee.full_name || 'N/A'"></div>
                                            <div class="text-sm text-secondary-500" x-text="employee.user?.email || 'N/A'"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-secondary-900" x-text="employee.position || 'N/A'"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-secondary-900" x-text="employee.manager?.full_name || 'Sem gestor'"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="employee.user?.is_active ? 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800' : 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800'">
                                        <span x-text="employee.user?.is_active ? 'Ativo' : 'Inativo'"></span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <button @click="editEmployee(employee.id)" class="text-blue-600 hover:text-blue-900 transition-colors" title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button @click="deleteEmployee(employee)" class="text-red-600 hover:text-red-900 transition-colors" title="Excluir" :disabled="deletingEmployee">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div x-show="!loading && employees.length === 0" class="p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-secondary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-secondary-900">Nenhum funcionário encontrado</h3>
                <p class="mt-2 text-sm text-secondary-500">Comece cadastrando seu primeiro funcionário</p>
                <div class="mt-6">
                    <a href="{{ route('employees.create') }}" class="btn-primary">
                        Adicionar Funcionário
                    </a>
                </div>
            </div>
        </div>
    </div>


</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('employeeManager', () => ({
        employees: [],
        loading: true,
        deletingEmployee: false,
        filters: {
            search: '',
            status: '',
            manager_id: ''
        },

        async init() {
            setTimeout(async () => {
                await this.loadEmployees();
            }, 100);
        },

        async loadEmployees() {
            this.loading = true;

            try {
                if (typeof window.apiRequest !== 'function') {
                    throw new Error('window.apiRequest not available');
                }

                const params = new URLSearchParams();
                params.append('page', '1');

                if (this.filters.search) params.append('search', this.filters.search);
                if (this.filters.status) params.append('is_active', this.filters.status);
                if (this.filters.manager_id) params.append('manager_id', this.filters.manager_id);

                const url = `/employees?${params}`;

                const result = await window.apiRequest(url, 'GET');

                if (result && result.success && result.data) {
                    this.employees = result.data;
                } else {
                    console.error('Invalid API response format:', result);
                    this.employees = [];
                }
            } catch (error) {
                console.error('Error loading employees:', error);
                this.employees = [];

                if (!error.message.includes('401') && !error.message.includes('Unauthenticated')) {
                    alert('Erro ao carregar funcionários: ' + error.message);
                }
            } finally {
                this.loading = false;
            }
        },

        editEmployee(employeeId) {
            window.location.href = `/admin/employees/${employeeId}/edit`;
        },

        async deleteEmployee(employee) {
            if (!confirm(`Tem certeza que deseja excluir o funcionário "${employee.full_name}"?\n\nEsta ação não pode ser desfeita.`)) {
                return;
            }

            this.deletingEmployee = true;

            try {
                await window.apiRequest(`/employees/${employee.id}`, 'DELETE');

                this.employees = this.employees.filter(emp => emp.id !== employee.id);

                alert('Funcionário excluído com sucesso!');
            } catch (error) {
                console.error('Erro ao excluir funcionário:', error);
                alert('Erro ao excluir funcionário: ' + error.message);
            } finally {
                this.deletingEmployee = false;
            }
        },

        formatDate(date) {
            if (!date) return 'N/A';
            return new Date(date).toLocaleDateString('pt-BR');
        }
    }));
});
</script>
@endpush
@endsection
