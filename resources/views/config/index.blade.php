@extends('layouts.app')

@section('headerVariant', 'v2')
@section('sidebarVariant', 'v2')

@section('contenido')
    @livewire('config.general', ['config' => $config, 'servicios' => $servicios])
@stop

@push('modals')
@endpush

{{-- section de js --}}
@section('js')
@stop
