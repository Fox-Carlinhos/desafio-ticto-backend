@extends('layouts.app')

@section('page-title', 'Registros do Funcionário')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-secondary-900">Registros do Funcionário</h1>
        <p class="mt-2 text-sm text-secondary-700">Visualizar registros de ponto específicos</p>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-secondary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-secondary-900">TODO</h3>
                <div class="mt-6">
                    <a href="{{ route('admin.time-records.index') }}" class="btn-primary">
                        Voltar para Lista
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
