@extends('layouts.app')

@section('title', 'Maintenance Report')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Maintenance Report</h1>
        <p class="greeting-subtitle">View completed maintenance activities</p>
    </div>
    
    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">Maintenance Records</h2>
        </div>
        <div class="data-table">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Request #</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" class="text-center">No maintenance records found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
