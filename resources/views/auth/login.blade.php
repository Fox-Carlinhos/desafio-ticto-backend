@extends('layouts.app')

@section('auth-content')
<div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <!-- Logo -->
            <div class="mx-auto w-16 h-16 rounded-2xl gradient-primary flex items-center justify-center mb-6 shadow-medium">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <h2 class="text-3xl font-bold text-secondary-900 mb-2">
                Bem-vindo ao Ponto Ticto
            </h2>
            <p class="text-secondary-600">
                Faça login para acessar o sistema de ponto eletrônico
            </p>
        </div>

        <!-- Login form -->
        <div class="card" x-data="loginForm()">
            <div class="card-body">
                <form @submit.prevent="handleSubmit">
                    <div class="space-y-6">
                        <!-- Email -->
                        <div>
                            <label for="email" class="form-label">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                </svg>
                                Email
                            </label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                x-model="email"
                                class="form-input"
                                placeholder="seu@email.com"
                                required
                                autocomplete="email"
                                :class="errors.email ? 'border-danger-300 focus:border-danger-500 focus:ring-danger-500' : ''"
                            >
                            <p x-show="errors.email" class="form-error" x-text="errors.email"></p>
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="form-label">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Senha
                            </label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                x-model="password"
                                class="form-input"
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                                :class="errors.password ? 'border-danger-300 focus:border-danger-500 focus:ring-danger-500' : ''"
                            >
                            <p x-show="errors.password" class="form-error" x-text="errors.password"></p>
                        </div>

                        <!-- Submit button -->
                        <div>
                            <button
                                type="submit"
                                :disabled="submitting"
                                class="w-full btn-primary btn-lg font-semibold"
                                :class="submitting ? 'opacity-75 cursor-not-allowed' : ''"
                            >
                                <span x-show="!submitting">Entrar</span>
                                <span x-show="submitting" class="flex items-center justify-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Autenticando...
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Demo info -->
        <div class="text-center space-y-2">
            <p class="text-xs text-secondary-500">
                © {{ date('Y') }} Ponto Ticto. Sistema de registro de ponto eletrônico.
            </p>
            <div class="text-xs text-secondary-400">
                <p><strong>Admin:</strong> admin@ticto.com.br / admin123</p>
                <p><strong>Funcionário:</strong> carlos.santos@ticto.com.br / carlos123</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('loginForm', () => ({
        email: '',
        password: '',
        errors: {},
        submitting: false,

        async handleSubmit() {
            this.submitting = true;
            this.errors = {};

            try {
                const response = await fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        email: this.email,
                        password: this.password
                    })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    localStorage.setItem('auth_token', result.data.access_token);
                    localStorage.setItem('user_data', JSON.stringify(result.data.user));

                    this.showToast('Login realizado com sucesso!', 'success');

                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 1000);
                } else {
                    this.showToast(result.message || 'Erro no login', 'error');
                    if (result.errors) {
                        this.errors = result.errors;
                    }
                }
            } catch (error) {
                console.error('Login error:', error);
                this.showToast('Erro de conexão com o servidor', 'error');
            } finally {
                this.submitting = false;
            }
        },

        showToast(message, type) {
            window.dispatchEvent(new CustomEvent('show-toast', {
                detail: { message, type }
            }));
        }
    }));
});
</script>
@endpush
@endsection
