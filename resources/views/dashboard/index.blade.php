@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div x-data="dashboard()" x-init="init()">
    <!-- Role-based dashboard -->
    <template x-if="isAdmin()">
        <div>
            @include('dashboard.partials.admin-dashboard')
        </div>
    </template>

    <template x-if="isEmployee()">
        <div>
            @include('dashboard.partials.employee-dashboard')
        </div>
    </template>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('dashboard', () => ({
        stats: {},
        loading: true,
        user: null,

        async init() {
            const userData = localStorage.getItem('user_data');
            if (userData) {
                try {
                    this.user = JSON.parse(userData);
                } catch (error) {
                    console.error('Error parsing user data:', error);
                }
            }

            await this.loadStats();
        },

        isAdmin() {
            return this.user?.role === 'admin';
        },

        isEmployee() {
            return this.user?.role === 'employee';
        },

        async loadStats() {
            this.loading = true;
            try {
                let result;
                if (this.isAdmin()) {
                    console.log('Loading admin stats...');
                    result = await window.apiRequest('/reports/summary');
                    console.log('Admin stats result:', result);

                    const data = result.data || {};
                    this.stats = {
                        total_employees: data.overview?.funcionarios_ativos || 0,
                        active_employees: data.overview?.funcionarios_ativos || 0,
                        today_records: this.getTodayRecordsFromMonthly(data.monthly_trend),
                        month_records: this.getCurrentMonthRecords(data.monthly_trend),
                        recent_records: this.formatRecentRecords(data.top_employees_this_month)
                    };
                } else {
                    result = await window.apiRequest('/time-records/summary');
                    this.stats = result.data || {};
                }

                console.log('Final stats:', this.stats);
            } catch (error) {
                console.error('Erro ao carregar estatísticas:', error);
                this.stats = {
                    total_employees: 0,
                    active_employees: 0,
                    today_records: 0,
                    month_records: 0,
                    recent_records: []
                };
            } finally {
                this.loading = false;
            }
        },

        getTodayRecordsFromMonthly(monthlyTrend) {
            if (!monthlyTrend || !Array.isArray(monthlyTrend)) return 0;
            const currentMonth = monthlyTrend[0];
            return Math.round((currentMonth?.total_registros || 0) / 30);
        },

        getCurrentMonthRecords(monthlyTrend) {
            if (!monthlyTrend || !Array.isArray(monthlyTrend)) return 0;
            return monthlyTrend[0]?.total_registros || 0;
        },

        formatRecentRecords(topEmployees) {
            if (!topEmployees || !Array.isArray(topEmployees)) return [];

            return topEmployees.slice(0, 5).map((emp, index) => ({
                id: index + 1,
                employee_name: emp.nome_funcionario,
                formatted_recorded_at: `${emp.total_registros} registros este mês`
            }));
        }
    }));
});
</script>
@endpush
@endsection

