@extends('layouts.vertical', ['title' => 'Dashboard'])
@section('html_attribute')
data-sidenav-color="dark"
@endsection
@section('css')

@endsection

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Dashboard'] )

    <livewire:painel.dashboard />
@endsection

@section('scripts')

@endsection
