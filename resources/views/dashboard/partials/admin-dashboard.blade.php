<!-- Admin Dashboard -->
<div class="space-y-8">
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-2xl shadow-large overflow-hidden">
        <div class="px-8 py-6 sm:py-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">
                        Olá, <span x-text="user?.name?.split(' ')[0] || 'Admin'"></span>! 👋
                    </h1>
                    <p class="text-primary-100 text-lg">
                        Bem-vindo ao painel administrativo do sistema de ponto
                    </p>
                </div>
                <div class="hidden sm:block">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Employees -->
        <div class="card hover:shadow-medium transition-shadow duration-200">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-secondary-600">Total de Funcionários</p>
                        <div class="flex items-baseline">
                            <p class="text-2xl font-bold text-secondary-900" x-show="!loading" x-text="stats.total_employees || 0"></p>
                            <div x-show="loading" class="skeleton h-8 w-12"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Employees -->
        <div class="card hover:shadow-medium transition-shadow duration-200">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-success-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-secondary-600">Funcionários Ativos</p>
                        <div class="flex items-baseline">
                            <p class="text-2xl font-bold text-secondary-900" x-show="!loading" x-text="stats.active_employees || 0"></p>
                            <div x-show="loading" class="skeleton h-8 w-12"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today Records -->
        <div class="card hover:shadow-medium transition-shadow duration-200">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-warning-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-secondary-600">Registros Hoje</p>
                        <div class="flex items-baseline">
                            <p class="text-2xl font-bold text-secondary-900" x-show="!loading" x-text="stats.today_records || 0"></p>
                            <div x-show="loading" class="skeleton h-8 w-12"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- This Month Records -->
        <div class="card hover:shadow-medium transition-shadow duration-200">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-secondary-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-secondary-600">Registros Este Mês</p>
                        <div class="flex items-baseline">
                            <p class="text-2xl font-bold text-secondary-900" x-show="!loading" x-text="stats.month_records || 0"></p>
                            <div x-show="loading" class="skeleton h-8 w-12"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Quick Actions Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold text-secondary-900">Ações Rápidas</h2>
            </div>
            <div class="card-body space-y-4">
                <a href="{{ route('employees.create') }}" class="flex items-center p-4 rounded-lg border border-secondary-200 hover:border-primary-300 hover:bg-primary-50 transition-all duration-200 group">
                    <div class="w-10 h-10 bg-primary-100 group-hover:bg-primary-200 rounded-lg flex items-center justify-center mr-4 transition-colors">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-medium text-secondary-900">Adicionar Funcionário</h3>
                        <p class="text-sm text-secondary-500">Cadastrar novo funcionário no sistema</p>
                    </div>
                </a>



                <a href="{{ route('reports.index') }}" class="flex items-center p-4 rounded-lg border border-secondary-200 hover:border-warning-300 hover:bg-warning-50 transition-all duration-200 group">
                    <div class="w-10 h-10 bg-warning-100 group-hover:bg-warning-200 rounded-lg flex items-center justify-center mr-4 transition-colors">
                        <svg class="w-5 h-5 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-medium text-secondary-900">Gerar Relatórios</h3>
                        <p class="text-sm text-secondary-500">Exportar dados e estatísticas</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold text-secondary-900">Atividade Recente</h2>
            </div>
            <div class="card-body">
                <div x-show="loading" class="space-y-4">
                    <div class="skeleton h-4 w-full"></div>
                    <div class="skeleton h-4 w-3/4"></div>
                    <div class="skeleton h-4 w-1/2"></div>
                </div>

                <div x-show="!loading && stats.recent_records" class="space-y-3">
                    <template x-for="record in stats.recent_records?.slice(0, 5)" :key="record.id">
                        <div class="flex items-center space-x-3 p-3 rounded-lg bg-secondary-50 hover:bg-secondary-100 transition-colors">
                            <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-secondary-900 truncate" x-text="record.employee_name"></p>
                                <p class="text-xs text-secondary-500" x-text="record.formatted_recorded_at"></p>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="!loading && (!stats.recent_records || stats.recent_records.length === 0)" class="text-center py-6">
                    <svg class="mx-auto h-12 w-12 text-secondary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="mt-2 text-sm text-secondary-500">Nenhum registro recente</p>
                </div>
            </div>
        </div>
    </div>
</div>

