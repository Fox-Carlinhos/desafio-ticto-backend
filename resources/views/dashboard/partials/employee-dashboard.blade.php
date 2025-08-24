<!-- Employee Dashboard -->
<div class="space-y-8" x-data="employeeDashboard()" x-init="init()">
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-success-600 to-success-700 rounded-2xl shadow-large overflow-hidden">
        <div class="px-8 py-6 sm:py-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">
                        Olá, <span x-text="user?.name?.split(' ')[0] || 'Funcionário'"></span>! 👋
                    </h1>
                    <p class="text-success-100 text-lg">
                        Bem-vindo ao seu painel de controle de ponto
                    </p>
                    <div class="mt-4 flex items-center text-success-100">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 12v-4m0 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2h4a2 2 0 002-2z"></path>
                        </svg>
                        <span x-data="{ time: new Date().toLocaleString('pt-BR') }"
                              x-init="setInterval(() => time = new Date().toLocaleString('pt-BR'), 1000)"
                              x-text="time">
                        </span>
                    </div>
                </div>
                <div class="hidden sm:block">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Punch Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Punch Button -->
        <div class="lg:col-span-1">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold text-secondary-900">Registrar Ponto</h2>
                </div>
                <div class="card-body text-center">
                    <div class="mb-6">
                        <div class="w-24 h-24 mx-auto bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center shadow-large hover:shadow-xl transition-all duration-300 cursor-pointer"
                             @click="recordPunch()"
                             :class="recording ? 'opacity-75 cursor-not-allowed' : ''"
                        >
                            <svg x-show="!recording" class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <svg x-show="recording" class="animate-spin w-12 h-12 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    <button
                        @click="recordPunch()"
                        :disabled="recording"
                        class="w-full btn-primary btn-lg font-semibold"
                        :class="recording ? 'opacity-75 cursor-not-allowed' : ''"
                    >
                        <span x-show="!recording">Bater Ponto</span>
                        <span x-show="recording">Registrando...</span>
                    </button>

                    <p class="text-xs text-secondary-500 mt-2">
                        Clique para registrar sua entrada/saída
                    </p>
                </div>
            </div>
        </div>

        <!-- Today Status -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-semibold text-secondary-900">Status de Hoje</h2>
                </div>
                <div class="card-body">
                    <div x-show="todayStatus" class="space-y-4">
                        <!-- Date and Records Count -->
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-secondary-900" x-text="todayStatus?.date"></h3>
                                <p class="text-sm text-secondary-500">
                                    <span x-text="todayStatus?.records_count || 0"></span> registros hoje
                                </p>
                            </div>
                            <span class="badge badge-info" x-text="todayStatus?.status"></span>
                        </div>

                        <!-- Records Timeline -->
                        <div x-show="summaryData?.today?.records && summaryData.today.records.length > 0" class="space-y-3">
                            <h4 class="text-sm font-medium text-secondary-700">Registros de Hoje:</h4>
                            <div class="space-y-2">
                                <template x-for="(record, index) in summaryData.today.records" :key="record.id">
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-secondary-50">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                                 :class="index % 2 === 0 ? 'bg-success-100 text-success-600' : 'bg-warning-100 text-warning-600'">
                                                <svg x-show="index % 2 === 0" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                                </svg>
                                                <svg x-show="index % 2 === 1" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-secondary-900" x-text="record.time"></p>
                                                <p class="text-xs text-secondary-500" x-text="index % 2 === 0 ? 'Entrada' : 'Saída'"></p>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- No records message -->
                        <div x-show="!summaryData?.today?.records || summaryData.today.records.length === 0" class="text-center py-6">
                            <svg class="mx-auto h-12 w-12 text-secondary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-secondary-500">Nenhum registro hoje</p>
                            <p class="text-xs text-secondary-400">Registre seu primeiro ponto</p>
                        </div>
                    </div>

                    <!-- Loading state -->
                    <div x-show="!todayStatus" class="animate-pulse space-y-4">
                        <div class="skeleton h-6 w-1/3"></div>
                        <div class="skeleton h-4 w-1/4"></div>
                        <div class="space-y-2">
                            <div class="skeleton h-12 w-full"></div>
                            <div class="skeleton h-12 w-full"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <!-- This Week -->
        <div class="card hover:shadow-medium transition-shadow duration-200">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 12v-4m0 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2h4a2 2 0 002-2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-secondary-600">Esta Semana</p>
                        <div class="flex items-baseline">
                            <p class="text-2xl font-bold text-secondary-900" x-show="!loadingStats" x-text="weekRecords || 0"></p>
                            <div x-show="loadingStats" class="skeleton h-8 w-12"></div>
                            <p class="ml-1 text-sm text-secondary-500">registros</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- This Month -->
        <div class="card hover:shadow-medium transition-shadow duration-200">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-success-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-secondary-600">Este Mês</p>
                        <div class="flex items-baseline">
                            <p class="text-2xl font-bold text-secondary-900" x-show="!loadingStats" x-text="summaryData?.this_month?.total_records || 0"></p>
                            <div x-show="loadingStats" class="skeleton h-8 w-12"></div>
                            <p class="ml-1 text-sm text-secondary-500">registros</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average per Day -->
        <div class="card hover:shadow-medium transition-shadow duration-200">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-warning-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-secondary-600">Média por Dia</p>
                        <div class="flex items-baseline">
                            <p class="text-2xl font-bold text-secondary-900" x-show="!loadingStats" x-text="averagePerDay || '0.0'"></p>
                            <div x-show="loadingStats" class="skeleton h-8 w-12"></div>
                            <p class="ml-1 text-sm text-secondary-500">registros</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h2 class="text-lg font-semibold text-secondary-900">Ações Rápidas</h2>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('time-records.index') }}" class="flex items-center p-4 rounded-lg border border-secondary-200 hover:border-primary-300 hover:bg-primary-50 transition-all duration-200 group">
                    <div class="w-10 h-10 bg-primary-100 group-hover:bg-primary-200 rounded-lg flex items-center justify-center mr-4 transition-colors">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-medium text-secondary-900">Ver Meus Registros</h3>
                        <p class="text-sm text-secondary-500">Histórico completo de pontos</p>
                    </div>
                </a>

                <a href="{{ route('profile') }}" class="flex items-center p-4 rounded-lg border border-secondary-200 hover:border-success-300 hover:bg-success-50 transition-all duration-200 group">
                    <div class="w-10 h-10 bg-success-100 group-hover:bg-success-200 rounded-lg flex items-center justify-center mr-4 transition-colors">
                        <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-medium text-secondary-900">Meu Perfil</h3>
                        <p class="text-sm text-secondary-500">Atualizar informações pessoais</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

