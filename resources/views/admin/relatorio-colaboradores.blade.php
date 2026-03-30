@extends('layouts.vertical', ['title' => 'Relatório de Produtividade'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Admin', 'title' => 'Relatório de Produtividade de Colaboradores'])

    <livewire:admin.relatorio-colaboradores />
@endsection
