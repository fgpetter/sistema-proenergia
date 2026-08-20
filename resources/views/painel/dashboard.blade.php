@extends('layouts.vertical', ['title' => 'Dashboard'])
@section('html_attribute')
data-sidenav-color="dark"
@endsection
@section('css')

@endsection

@section('content')
    <livewire:painel.dashboard />
@endsection

@section('scripts')
    @vite(['resources/js/pages/painel-dashboard-charts.js'])
@endsection
