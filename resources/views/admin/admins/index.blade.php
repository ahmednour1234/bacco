@extends('layouts.admin-app')

@section('title', 'Admins – Qimta Admin')
@section('page-title', 'Admin Management')

@section('breadcrumb')
    <span class="text-xs text-slate-400">Management</span>
    <span class="text-xs text-slate-300">/</span>
    <span class="text-xs font-medium text-slate-600">Admins</span>
@endsection

@section('content')
    <livewire:admin.admins.index-table />
@endsection
