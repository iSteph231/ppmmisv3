@extends('layouts.app')

@section('title', 'Inventory')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Inventory</h1>
        <p class="greeting-subtitle">Supplies and equipment inventory.</p>
    </div>

    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">Inventory Items</h2>
        </div>
        <div class="empty-state">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8l-9-5-9 5m18 0v8l-9 5m9-13l-9 5M3 8v8l9 5M3 8l9 5m0 0v8M7.5 5.5l9 5"/>
            </svg>
            <p>Inventory management is not configured yet.</p>
        </div>
    </div>
</div>
@endsection
