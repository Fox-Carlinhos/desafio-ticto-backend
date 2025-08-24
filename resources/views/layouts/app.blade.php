<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-secondary-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sistema de Ponto') }} @yield('title')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/heroicons@2.0.18/24/outline/style.css">

        <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased" x-data="{ sidebarOpen: false }" x-cloak>
    <!-- Toast Container -->
    <div x-data="toastManager()" class="fixed top-4 right-4 z-50 space-y-2">
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-show="true"
                x-transition:enter="transform ease-out duration-300 transition"
                x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                :class="toast.type === 'success' ? 'toast-success' : toast.type === 'error' ? 'toast-error' : toast.type === 'warning' ? 'toast-warning' : 'toast'"
                class="max-w-sm w-full bg-white shadow-large rounded-lg border border-secondary-200 overflow-hidden"
            >
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <!-- Success Icon -->
                            <svg x-show="toast.type === 'success'" class="h-6 w-6 text-success-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <!-- Error Icon -->
                            <svg x-show="toast.type === 'error'" class="h-6 w-6 text-danger-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <!-- Warning Icon -->
                            <svg x-show="toast.type === 'warning'" class="h-6 w-6 text-warning-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.134 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                            <!-- Info Icon -->
                            <svg x-show="!toast.type || toast.type === 'info'" class="h-6 w-6 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1">
                            <p class="text-sm font-medium text-secondary-900" x-text="toast.message"></p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="removeToast(toast.id)" class="bg-white rounded-md inline-flex text-secondary-400 hover:text-secondary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                <span class="sr-only">Fechar</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

        <div x-data="authLayout()" x-init="init()" class="h-full">
        <!-- Check if user is logged in -->
        <template x-if="!isLoggedIn">
            @yield('auth-content')
        </template>

        <template x-if="isLoggedIn">
            <div class="h-full">
                <!-- Off-canvas menu for mobile -->
                <div x-show="sidebarOpen" class="relative z-50 lg:hidden" x-description="Off-canvas menu for mobile, show/hide based on off-canvas menu state.">
                    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-secondary-600 bg-opacity-75"></div>

                    <div class="fixed inset-0 z-50 flex">
                        <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" x-description="Off-canvas menu, show/hide based on off-canvas menu state." class="relative mr-16 flex w-full max-w-xs flex-1" @click.away="sidebarOpen = false">
                            <div x-show="sidebarOpen" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-description="Close button, show/hide based on off-canvas menu state." class="absolute left-full top-0 flex w-16 justify-center pt-5">
                                <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                                    <span class="sr-only">Fechar sidebar</span>
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <!-- Sidebar component for mobile -->
                            @include('layouts.partials.sidebar')
                        </div>
                    </div>
                </div>

                <!-- Static sidebar for desktop -->
                <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
                    @include('layouts.partials.sidebar')
                </div>

                <div class="lg:pl-72">
                    <!-- Top navigation -->
                    @include('layouts.partials.header')

                    <!-- Main content -->
                    <main class="py-6">
                        <div class="px-4 sm:px-6 lg:px-8">
                            @yield('content')
                        </div>
                    </main>
                </div>
            </div>
        </template>
    </div>

    <!-- Scripts -->
    @stack('scripts')

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('authLayout', () => ({
            isLoggedIn: false,
            user: null,
            token: null,

            init() {
                // Check if user is logged in
                this.token = localStorage.getItem('auth_token');
                const userData = localStorage.getItem('user_data');

                if (this.token && userData) {
                    try {
                        this.user = JSON.parse(userData);
                        this.isLoggedIn = true;

                        const currentPath = window.location.pathname;
                        if (currentPath === '/login' || currentPath === '/') {
                            window.location.href = '/dashboard';
                        }
                    } catch (error) {
                        console.error('Error parsing user data:', error);
                        this.logout();
                    }
                } else {
                    const currentPath = window.location.pathname;
                    if (currentPath !== '/login') {
                        window.location.href = '/login';
                    }
                }
            },

            logout() {
                if (this.token) {
                    fetch('/api/logout', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${this.token}`,
                            'Accept': 'application/json'
                        }
                    }).catch(error => console.error('Logout error:', error));
                }

                localStorage.removeItem('auth_token');
                localStorage.removeItem('user_data');
                this.isLoggedIn = false;
                this.user = null;
                this.token = null;
                window.location.href = '/login';
            },

            isAdmin() {
                return this.user?.role === 'admin';
            },

            isEmployee() {
                return this.user?.role === 'employee';
            }
        }));
    });
    </script>
</body>
</html>

