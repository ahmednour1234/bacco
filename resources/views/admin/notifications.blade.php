@extends('layouts.admin-app')

@section('title', __('app.notifications') . ' – Qimta')
@section('page-title', __('app.notifications'))

@section('content')
    <livewire:notification-list />
@endsection
