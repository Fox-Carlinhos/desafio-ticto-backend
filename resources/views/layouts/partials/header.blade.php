<!-- Header -->
<div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-secondary-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
    <!-- Mobile menu button -->
    <button type="button" class="-m-2.5 p-2.5 text-secondary-700 lg:hidden" @click="sidebarOpen = true">
        <span class="sr-only">Abrir sidebar</span>
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
    </button>

    <!-- Separator -->
    <div class="h-6 w-px bg-secondary-200 lg:hidden"></div>

    <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
        <!-- Page title -->
        <div class="flex items-center">
            <h1 class="text-lg font-semibold text-secondary-900">
                @yield('page-title', 'Dashboard')
            </h1>
        </div>

        <!-- Right side -->
        <div class="flex items-center gap-x-4 lg:gap-x-6 ml-auto">
            <!-- Current time -->
            <div class="hidden sm:flex items-center text-sm text-secondary-600">
                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span x-data="{ time: new Date().toLocaleTimeString('pt-BR') }"
                      x-init="setInterval(() => time = new Date().toLocaleTimeString('pt-BR'), 1000)"
                      x-text="time">
                </span>
            </div>

            <!-- Current date -->
            <div class="hidden sm:flex items-center text-sm text-secondary-600">
                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
                {{ now()->format('d/m/Y') }}
            </div>

            <!-- Separator -->
            <div class="h-6 w-px bg-secondary-200"></div>

            <!-- User menu -->
            <div class="relative" x-data="{ open: false }">
                <button type="button" class="flex items-center gap-x-3 text-sm leading-6 text-secondary-900" @click="open = !open">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100">
                        <span class="text-sm font-medium text-primary-700" x-text="user?.name?.charAt(0) || 'U'"></span>
                    </div>
                    <span class="hidden lg:flex lg:items-center">
                        <span class="font-semibold" x-text="user?.name"></span>
                        <svg class="ml-2 h-5 w-5 text-secondary-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </button>

                <!-- User dropdown -->
                <div x-show="open"
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 z-10 mt-2.5 w-48 origin-top-right rounded-lg bg-white py-2 shadow-large ring-1 ring-secondary-900/5 focus:outline-none">

                    <!-- User info -->
                    <div class="px-3 py-2 border-b border-secondary-100">
                        <p class="text-sm font-medium text-secondary-900" x-text="user?.name"></p>
                        <p class="text-xs text-secondary-500" x-text="user?.email"></p>
                        <p class="text-xs text-primary-600 mt-1">
                            <span x-text="user?.role === 'admin' ? 'Administrador' : 'Funcionário'"></span>
                        </p>
                    </div>

                    <!-- Menu items -->
                    <a href="{{ route('profile') }}" class="block px-3 py-2 text-sm text-secondary-700 hover:bg-secondary-50 hover:text-secondary-900">
                        <div class="flex items-center">
                            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Meu Perfil
                        </div>
                    </a>

                    <a href="{{ route('settings') }}" class="block px-3 py-2 text-sm text-secondary-700 hover:bg-secondary-50 hover:text-secondary-900">
                        <div class="flex items-center">
                            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Configurações
                        </div>
                    </a>

                    <div class="border-t border-secondary-100 mt-2 pt-2">
                        <button @click="logout(); window.location.reload()" class="block w-full text-left px-3 py-2 text-sm text-danger-700 hover:bg-danger-50">
                            <div class="flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                </svg>
                                Sair
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

