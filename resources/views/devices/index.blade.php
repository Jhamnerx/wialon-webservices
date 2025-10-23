@extends('layouts.app')

@section('headerVariant', 'v2')
@section('sidebarVariant', 'v2')

@section('contenido')
    @livewire('devices.index')
@stop

@push('modals')
@endpush

{{-- section de js --}}
@section('js')
@stop
