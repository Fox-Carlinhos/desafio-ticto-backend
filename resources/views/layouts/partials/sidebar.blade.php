<!-- Sidebar -->
<div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white border-r border-secondary-200 px-6 pb-4 shadow-sm">
    <!-- Logo -->
    <div class="flex h-16 shrink-0 items-center">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-lg gradient-primary flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-lg font-bold text-secondary-900">Ponto Ticto</h1>
                <p class="text-xs text-secondary-500">Sistema de Ponto</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex flex-1 flex-col">
        <ul role="list" class="flex flex-1 flex-col gap-y-7">
            <li>
                <ul role="list" class="-mx-2 space-y-1">
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('dashboard') }}"
                           class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : 'nav-link-inactive' }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                            Dashboard
                        </a>
                    </li>

                    <!-- Employee Section: Time Records -->
                    <template x-if="isEmployee()">
                        <div class="space-y-1">

                            <!-- My Time Records -->
                            <li>
                                <a href="{{ route('time-records.index') }}"
                                   class="nav-link {{ request()->routeIs('time-records.*') ? 'nav-link-active' : 'nav-link-inactive' }}">
                                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Meus Registros
                                </a>
                            </li>
                        </div>
                    </template>

                    <!-- Admin Section -->
                    <template x-if="isAdmin()">
                        <div class="space-y-1">
                            <!-- Employees -->
                            <li>
                                <a href="{{ route('employees.index') }}"
                                   class="nav-link {{ request()->routeIs('employees.*') ? 'nav-link-active' : 'nav-link-inactive' }}">
                                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                    </svg>
                                    Funcionários
                                </a>
                            </li>



                            <!-- Reports -->
                            <li>
                                <a href="{{ route('reports.index') }}"
                                   class="nav-link {{ request()->routeIs('reports.*') ? 'nav-link-active' : 'nav-link-inactive' }}">
                                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                    </svg>
                                    Relatórios
                                </a>
                            </li>
                        </div>
                    </template>
                </ul>
            </li>

            <!-- User section -->
            <li class="mt-auto">
                <div class="border-t border-secondary-200 pt-4">
                    <!-- User info -->
                    <div class="flex items-center gap-x-3 mb-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100">
                            <span class="text-sm font-medium text-primary-700" x-text="user?.name?.charAt(0) || 'U'"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-secondary-900 truncate" x-text="user?.name"></p>
                            <p class="text-xs text-secondary-500 truncate">
                                <span x-text="user?.role === 'admin' ? 'Administrador' : 'Funcionário'"></span>
                            </p>
                        </div>
                    </div>

                    <!-- User actions -->
                    <div class="space-y-1">
                        <a href="{{ route('profile') }}" class="nav-link-inactive">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Meu Perfil
                        </a>

                        <button @click="logout(); window.location.reload()" class="w-full nav-link-inactive">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            Sair
                        </button>
                    </div>
                </div>
            </li>
        </ul>
    </nav>
</div>

