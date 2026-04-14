@extends('layouts.admin-app')

@section('title', __('app.supplier_products_approval') . ' – Qimta')
@section('page-title', __('app.supplier_products_approval'))

@section('content')
    <livewire:admin.suppliers.product-approval />
@endsection
