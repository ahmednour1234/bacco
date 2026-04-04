@extends('layouts.admin-app')

@section('content')
    <livewire:admin.orders.show-order :uuid="$uuid" />
@endsection
