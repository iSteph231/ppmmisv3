@extends('layouts.app')

@section('title', 'Users Management')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">User Management</h1>
        <p class="greeting-subtitle">Manage system users and roles</p>
    </div>

    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">All Users</h2>
            <a href="{{ route('users.create') }}" class="btn-create">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add User
            </a>
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
                        <th>Actions</th>
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
                        <td>
                            <a href="{{ route('users.edit', $user->id) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 2rem;">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection