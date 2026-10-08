@extends('layouts.app')

@section('title', 'Inventory')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Inventory</h1>
        <p class="greeting-subtitle">Select an inventory type to enter and view records.</p>
    </div>
    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">Inventory Types</h2>
        </div>
        <div class="p-6">
            <ol class="list-decimal space-y-4 pl-6">
                @foreach ($forms as $slug => $name)
                    <li><a href="{{ route('inventory.'.$slug) }}" class="text-blue-600 hover:underline">{{ $name }}</a></li>
                @endforeach
            </ol>
        </div>
    </div>
</div>
@endsection
