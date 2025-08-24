@extends('layouts.app')

@section('page-title', 'Relatórios')

@section('content')
<div class="max-w-6xl mx-auto" x-data="reportsPage()">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-secondary-900">Relatórios e Estatísticas</h1>
        <p class="mt-2 text-sm text-secondary-700">Gere relatórios detalhados e análises do sistema de ponto</p>
    </div>

    <!-- Action Buttons -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card">
            <div class="card-body text-center">
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-secondary-900 mb-2">Registros de Ponto</h3>
                <p class="text-sm text-secondary-600 mb-4">Visualize todos os registros de entrada e saída</p>
                <button @click="loadTimeRecords()" class="btn-primary w-full" :disabled="loadingRecords">
                    <span x-show="!loadingRecords">Ver Registros</span>
                    <span x-show="loadingRecords" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Carregando...
                    </span>
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-secondary-900 mb-2">Resumo Estatístico</h3>
                <p class="text-sm text-secondary-600 mb-4">Análises e estatísticas do sistema</p>
                <button @click="showSummary()" class="btn-success w-full" :disabled="loadingStats">
                    <span x-show="!loadingStats">Ver Resumo</span>
                    <span x-show="loadingStats" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Carregando...
                    </span>
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body text-center">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-secondary-900 mb-2">Exportar CSV</h3>
                <p class="text-sm text-secondary-600 mb-4">Baixe os dados em formato planilha</p>
                <button @click="downloadCSV()" class="btn-warning w-full" :disabled="loadingCSV">
                    <span x-show="!loadingCSV">Baixar CSV</span>
                    <span x-show="loadingCSV" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Baixando...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Time Records List -->
    <div x-show="showRecords" class="card mb-6">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-secondary-900">Registros de Ponto</h3>
            <p class="text-sm text-secondary-600">Período: 01/05/2025 a 23/08/2025</p>
        </div>
        <div class="card-body">
            <div x-show="loadingRecords" class="text-center py-8">
                <svg class="animate-spin mx-auto h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="mt-2 text-secondary-600">Carregando registros...</p>
            </div>
            <div x-show="!loadingRecords && records.length > 0" class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="table-header-cell">ID</th>
                            <th class="table-header-cell">Funcionário</th>
                            <th class="table-header-cell">Cargo</th>
                            <th class="table-header-cell">Gestor</th>
                            <th class="table-header-cell">Data/Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="record in records" :key="record.id_registro">
                            <tr>
                                <td class="table-cell font-mono text-sm" x-text="record.id_registro"></td>
                                <td class="table-cell font-medium" x-text="record.nome_funcionario"></td>
                                <td class="table-cell text-secondary-600" x-text="record.cargo"></td>
                                <td class="table-cell text-secondary-600" x-text="record.nome_gestor"></td>
                                <td class="table-cell font-mono text-sm" x-text="record.data_hora_completa"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <!-- Pagination Info -->
                <div x-show="pagination" class="mt-4 text-sm text-secondary-600 text-center">
                    Mostrando <span x-text="pagination?.from || 0"></span> a <span x-text="pagination?.to || 0"></span>
                    de <span x-text="pagination?.total || 0"></span> registros
                    (Página <span x-text="pagination?.current_page || 1"></span> de <span x-text="pagination?.last_page || 1"></span>)
                </div>
            </div>
            <div x-show="!loadingRecords && records.length === 0" class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-secondary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="mt-2 text-secondary-500">Nenhum registro encontrado</p>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div x-show="showStats" class="space-y-6">
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-secondary-900">Resumo Geral</h3>
            </div>
            <div class="card-body">
                <div x-show="loadingStats" class="text-center py-8">
                    <svg class="animate-spin mx-auto h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-2 text-secondary-600">Carregando estatísticas...</p>
                </div>
                <div x-show="!loadingStats" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-blue-50 p-6 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-medium text-blue-900">Total de Registros</h4>
                                <p class="text-2xl font-bold text-blue-600" x-text="stats.overview?.total_registros || 0"></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 p-6 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-medium text-green-900">Funcionários Ativos</h4>
                                <p class="text-2xl font-bold text-green-600" x-text="stats.overview?.funcionarios_ativos || 0"></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 p-6 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-medium text-yellow-900">Gestores Ativos</h4>
                                <p class="text-2xl font-bold text-yellow-600" x-text="stats.overview?.gestores_ativos || 0"></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-purple-50 p-6 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-medium text-purple-900">Período</h4>
                                <p class="text-sm font-bold text-purple-600">
                                    <span x-text="stats.overview?.primeiro_registro || 'N/A'"></span><br>
                                    <span class="text-xs">a</span><br>
                                    <span x-text="stats.overview?.ultimo_registro || 'N/A'"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Employees -->
        <div class="card" x-show="stats.top_employees_this_month && stats.top_employees_this_month.length > 0">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-secondary-900">Top Funcionários do Mês</h3>
            </div>
            <div class="card-body">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="table-header-cell">Funcionário</th>
                                <th class="table-header-cell">Cargo</th>
                                <th class="table-header-cell">Gestor</th>
                                <th class="table-header-cell">Registros</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(employee, index) in stats.top_employees_this_month?.slice(0, 10)" :key="index">
                                <tr>
                                    <td class="table-cell font-medium" x-text="employee.nome_funcionario"></td>
                                    <td class="table-cell text-secondary-600" x-text="employee.cargo"></td>
                                    <td class="table-cell text-secondary-600" x-text="employee.nome_gestor"></td>
                                    <td class="table-cell">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800" x-text="employee.total_registros"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('reportsPage', () => ({
        showRecords: false,
        showStats: false,
        loadingRecords: false,
        loadingStats: false,
        loadingCSV: false,
        records: [],
        stats: {},
        pagination: null,

        async loadTimeRecords() {
            this.showRecords = true;
            this.showStats = false;
            this.loadingRecords = true;

            try {
                const result = await window.apiRequest('/reports/time-records?start_date=2025-05-01&end_date=2025-08-23');

                if (result.success) {
                    this.records = result.data || [];
                    this.pagination = result.pagination || null;
                } else {
                    console.error('API returned success=false:', result);
                    this.records = [];
                }
            } catch (error) {
                console.error('Error loading records:', error);
                this.records = [];
                alert('Erro ao carregar registros: ' + error.message);
            } finally {
                this.loadingRecords = false;
            }
        },

        async showSummary() {
            this.showStats = true;
            this.showRecords = false;
            this.loadingStats = true;

            try {
                const result = await window.apiRequest('/reports/summary');

                if (result.success) {
                    this.stats = result.data || {};
                } else {
                    console.error('API returned success=false:', result);
                    this.stats = {};
                }
            } catch (error) {
                console.error('Error loading summary:', error);
                this.stats = {
                    overview: {
                        total_registros: 0,
                        funcionarios_ativos: 0,
                        gestores_ativos: 0,
                        primeiro_registro: 'N/A',
                        ultimo_registro: 'N/A'
                    }
                };
                alert('Erro ao carregar resumo: ' + error.message);
            } finally {
                this.loadingStats = false;
            }
        },

        async downloadCSV() {
            this.loadingCSV = true;

            try {
                const result = await window.apiRequest('/reports/export?start_date=2025-05-01&end_date=2025-08-23&format=csv');

                if (result.success && result.data && result.data.length > 0) {
                    let csvContent = "data:text/csv;charset=utf-8,\uFEFF";
                    csvContent += "ID do Registro,Nome do Funcionário,Cargo,Idade,Nome do Gestor,Data e Hora Completa,Data,Hora\n";

                    result.data.forEach(row => {
                        const csvRow = [
                            row["ID do Registro"] || row.id_registro || '',
                            `"${(row["Nome do Funcionário"] || row.nome_funcionario || '').replace(/"/g, '""')}"`,
                            `"${(row["Cargo"] || row.cargo || '').replace(/"/g, '""')}"`,
                            row["Idade"] || row.idade || '',
                            `"${(row["Nome do Gestor"] || row.nome_gestor || '').replace(/"/g, '""')}"`,
                            `"${(row["Data e Hora Completa"] || row.data_hora_completa || '').replace(/"/g, '""')}"`,
                            `"${(row["Data"] || this.formatDate(row.data_hora_completa) || '').replace(/"/g, '""')}"`,
                            `"${(row["Hora"] || this.formatTime(row.data_hora_completa) || '').replace(/"/g, '""')}"`
                        ].join(',');
                        csvContent += csvRow + '\n';
                    });

                    const encodedUri = encodeURI(csvContent);
                    const link = document.createElement("a");
                    link.setAttribute("href", encodedUri);
                    link.setAttribute("download", `relatorio-ponto-${new Date().toISOString().split('T')[0]}.csv`);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    alert('CSV baixado com sucesso!');
                } else {
                    console.error('No data to export:', result);
                    alert('Nenhum dado encontrado para exportar');
                }
            } catch (error) {
                console.error('Error downloading CSV:', error);
                alert('Erro ao baixar CSV: ' + error.message);
            } finally {
                this.loadingCSV = false;
            }
        },

        formatDate(dateString) {
            if (!dateString) return 'N/A';
            try {
                return new Date(dateString).toLocaleDateString('pt-BR');
            } catch {
                return dateString.split(' ')[0] || 'N/A';
            }
        },

        formatTime(dateString) {
            if (!dateString) return 'N/A';
            try {
                return new Date(dateString).toLocaleTimeString('pt-BR');
            } catch {
                return dateString.split(' ')[1] || 'N/A';
            }
        }
    }));
});
</script>
@endpush
@endsection
