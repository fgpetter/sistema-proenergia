@extends('layouts.vertical', ['title' => 'Gestão de Projetos'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Admin', 'title' => 'Gestão de Projetos'])

    @livewire('admin.projetos-list')
@endsection
