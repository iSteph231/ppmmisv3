@extends('layouts.app')

@section('title', 'Inventory')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/inventory.css') }}">
@endpush

@section('content')
<div class="content-wrapper inventory-page">
    <div class="greeting-section">
        <h1 class="greeting-title">Inventory</h1>
        <p class="greeting-subtitle">Select an inventory type to enter and view records.</p>
    </div>
    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">Inventory Types</h2><span class="inventory-type-count">{{ count($forms) }} forms</span>
        </div>
        <div class="p-6">
            <ol class="list-decimal inventory-types">
                @foreach ($forms as $slug => $name)
                    <li><a href="{{ route('inventory.'.$slug) }}" class="inventory-type-link"><span>{{ $name }}</span><span class="inventory-type-description">Data entry &amp; PDF export</span></a></li>
                @endforeach
            </ol>
        </div>
    </div>
</div>
@endsection
