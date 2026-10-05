@extends('layouts.app')

@section('title', 'Users Management')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">User Management</h1>
        <p class="greeting-subtitle">View system users and roles</p>
    </div>

    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">All Users</h2>
        </div>
        
        <div class="data-table">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users ?? [] as $user)
                    <tr>
                        <td>#{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge">{{ ucfirst($user->role) }}</span></td>
                        <td>
                            <span class="badge {{ $user->is_active ? 'badge-completed' : 'badge-pending' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 2rem;">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
