@extends('layouts.vertical', ['title' => 'Checklist de Análise'])

@section('content')
    @include('layouts.partials/page-title', [
        'subtitle' => 'Admin',
        'title' => 'Checklist de Análise — ' . Str::limit($projeto->nome, 80),
    ])

    @livewire('admin.projeto-checklist', ['projeto' => $projeto])
@endsection
