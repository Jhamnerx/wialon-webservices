@extends('layouts.app')

@section('headerVariant', 'v2')
@section('sidebarVariant', 'v2')

@section('contenido')
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Logs de Reenvío Historial</h1>
            </div>

        </div>

        <!-- Cards -->
        <div class="grid grid-cols-12 gap-6">

            <!-- Table (Logs) -->
            <div class="col-span-full xl:col-span-12 bg-white dark:bg-gray-800 shadow-sm rounded-xl">
                <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100">Historial de Reenvíos</h2>
                </header>
                <div class="p-3">
                    <!-- Livewire Component -->
                    @livewire('logs.reenvio')
                </div>
            </div>

        </div>

    </div>
@endsection
{{-- section de js --}}
@section('js')

@stop
