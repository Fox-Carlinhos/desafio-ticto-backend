@extends('layouts.app')

@section('page-title', 'Meu Perfil')

@section('content')
<div x-data="profileManager()" x-init="init()">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-secondary-900">Meu Perfil</h1>
        <p class="mt-2 text-sm text-secondary-700">Visualize suas informações pessoais</p>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-8">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto"></div>
        <p class="mt-4 text-secondary-600">Carregando perfil...</p>
    </div>

    <!-- Profile Content -->
    <div x-show="!loading" class="space-y-6">
        <!-- Personal Information Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold text-secondary-900">Informações Pessoais</h2>
                <p class="text-sm text-secondary-600">Suas informações de usuário</p>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nome -->
                    <div>
                        <label class="label">Nome de Usuário</label>
                        <div class="input bg-secondary-50" x-text="profile?.user?.name || 'Carregando...'"></div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="label">Email</label>
                        <div class="input bg-secondary-50" x-text="profile?.user?.email || 'Carregando...'"></div>
                    </div>

                    <!-- Data de Nascimento (apenas para funcionários) -->
                    <div x-show="profile?.employee">
                        <label class="label">Data de Nascimento</label>
                        <div class="input bg-secondary-50" x-text="formatBirthDate(profile?.employee?.birth_date)"></div>
                    </div>

                    <!-- Idade (apenas para funcionários) -->
                    <div x-show="profile?.employee">
                        <label class="label">Idade</label>
                        <div class="input bg-secondary-50" x-text="profile?.employee?.age + ' anos' || 'Calculando...'"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Information Card -->
        <div class="card" x-show="profile?.employee || profile?.admin">
            <div class="card-header">
                <h2 class="text-lg font-semibold text-secondary-900">Informações Profissionais</h2>
                <p class="text-sm text-secondary-600">Informações controladas pela administração</p>
            </div>
            <div class="card-body">
                <!-- Employee Information -->
                <div x-show="profile?.employee" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nome Completo -->
                        <div>
                            <label class="label">Nome Completo</label>
                            <div class="input bg-secondary-50" x-text="profile?.employee?.full_name || 'Carregando...'"></div>
                        </div>

                        <!-- CPF -->
                        <div>
                            <label class="label">CPF</label>
                            <div class="input bg-secondary-50" x-text="profile?.employee?.cpf || 'Carregando...'"></div>
                        </div>

                        <!-- Cargo -->
                        <div>
                            <label class="label">Cargo</label>
                            <div class="input bg-secondary-50" x-text="profile?.employee?.position || 'Carregando...'"></div>
                        </div>

                        <!-- Gestor -->
                        <div>
                            <label class="label">Gestor</label>
                            <div class="input bg-secondary-50" x-text="profile?.employee?.manager?.name || 'Sem gestor definido'"></div>
                        </div>
                    </div>

                    <!-- Address Section -->
                    <div x-show="profile?.employee?.address" class="mt-6 pt-6 border-t border-secondary-200">
                        <h3 class="text-lg font-medium text-secondary-900 mb-4">Endereço</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="label">Endereço Completo</label>
                                <div class="input bg-secondary-50" x-text="profile?.employee?.address?.full_address || 'Endereço não cadastrado'"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Information -->
                <div x-show="profile?.admin" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Role -->
                        <div>
                            <label class="label">Função</label>
                            <div class="input bg-secondary-50">Administrador</div>
                        </div>

                        <!-- Managed Employees Count -->
                        <div>
                            <label class="label">Funcionários Gerenciados</label>
                            <div class="input bg-secondary-50" x-text="profile?.admin?.managed_employees_count + ' funcionários' || '0 funcionários'"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('profileManager', () => ({
        loading: true,
        profile: null,

        async init() {
            await this.loadProfile();
        },

        async loadProfile() {
            this.loading = true;
            try {
                const result = await window.apiRequest('/profile', 'GET');
                if (result.success) {
                    this.profile = result.data;
                }
            } catch (error) {
                console.error('Error loading profile:', error);
                alert('Erro ao carregar perfil: ' + error.message);
            } finally {
                this.loading = false;
            }
        },

        formatBirthDate(dateString) {
            if (!dateString) return 'Não informado';

            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return 'Data inválida';

                return date.toLocaleDateString('pt-BR', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            } catch (error) {
                return 'Data inválida';
            }
        },

        isAdmin() {
            return this.profile?.admin !== undefined;
        },

        isEmployee() {
            return this.profile?.employee !== undefined;
        }
    }));
});
</script>
@endpush
@endsection
