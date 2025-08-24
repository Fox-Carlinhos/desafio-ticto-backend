@extends('layouts.app')

@section('page-title', 'Meus Registros de Ponto')

@section('content')
<div class="max-w-6xl mx-auto" x-data="timeRecordsPage()">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-secondary-900">Meus Registros de Ponto</h1>
        <p class="mt-2 text-sm text-secondary-700">Acompanhe seu histórico de registros de entrada e saída</p>
    </div>

    <!-- Quick Punch Section -->
    <div class="card mb-6" x-data="timeRecord()">
        <div class="card-header">
            <h2 class="text-lg font-semibold text-secondary-900">Registrar Ponto Rápido</h2>
        </div>
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-secondary-600">Registre sua entrada ou saída</p>
                    <p class="text-xs text-secondary-500 mt-1" x-show="todayStatus">
                        Hoje: <span x-text="todayStatus?.records_count || 0"></span> registros
                    </p>
                </div>
                <button
                    @click="recordPunch()"
                    :disabled="recording"
                    class="btn-primary"
                    :class="recording ? 'opacity-75 cursor-not-allowed' : ''"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span x-show="!recording">Bater Ponto</span>
                    <span x-show="recording">Registrando...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Action Button -->
    <div class="grid grid-cols-1 mb-8">
        <div class="card">
            <div class="card-body text-center">
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-secondary-900 mb-2">Visualizar Registros</h3>
                <p class="text-sm text-secondary-600 mb-4">Veja todos os seus registros de entrada e saída</p>
                <button @click="loadTimeRecords()" class="btn-primary w-full" :disabled="loadingRecords">
                    <span x-show="!loadingRecords">Ver Meus Registros</span>
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
    </div>

    <!-- Records Display -->
    <div x-show="showRecords" class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-secondary-900">Meus Registros de Ponto</h3>
            <span class="text-sm text-secondary-600" x-text="`${records.length} registros encontrados`"></span>
        </div>
        <div class="card-body">
            <div x-show="loadingRecords" class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mx-auto"></div>
                <p class="mt-2 text-secondary-600">Carregando registros...</p>
            </div>

            <div x-show="!loadingRecords && records.length === 0" class="text-center py-8">
                <svg class="w-12 h-12 text-secondary-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-secondary-600">Nenhum registro encontrado no período</p>
            </div>

            <div x-show="!loadingRecords && records.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-secondary-200">
                    <thead class="bg-secondary-50">
                        <tr>
                            <th class="w-1/6 px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">ID</th>
                            <th class="w-2/6 px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Data</th>
                            <th class="w-1/6 px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Hora</th>
                            <th class="w-2/6 px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Data/Hora Completa</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-secondary-200">
                        <template x-for="record in records" :key="record.id">
                            <tr class="hover:bg-secondary-50">
                                <td class="w-1/6 px-6 py-4 whitespace-nowrap text-sm font-medium text-secondary-900" x-text="record.id"></td>
                                <td class="w-2/6 px-6 py-4 whitespace-nowrap text-sm text-secondary-900" x-text="record.date"></td>
                                <td class="w-1/6 px-6 py-4 whitespace-nowrap text-sm text-secondary-900" x-text="record.time"></td>
                                <td class="w-2/6 px-6 py-4 whitespace-nowrap text-sm text-secondary-500" x-text="record.recorded_at"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('timeRecordsPage', () => ({
        showRecords: false,
        loadingRecords: false,
        records: [],
        pagination: null,

        async loadTimeRecords() {
            this.showRecords = true;
            this.loadingRecords = true;

            try {
                const result = await window.apiRequest('/time-records?start_date=2025-05-01&end_date=2025-08-23&page=1');

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
        }
    }));
});
</script>
@endpush
@endsection
