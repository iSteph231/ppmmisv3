@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Users</h1>
        <p class="greeting-subtitle">View registered users and assign their roles. Changes save automatically.</p>
    </div>

    @if(session('success'))
        <div role="status" class="mb-4 rounded-lg bg-green-50 p-4 text-green-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="mb-4 rounded-lg bg-red-50 p-4 text-red-800">{{ $errors->first() }}</div>
    @endif

    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">All Users</h2>
        </div>
        
        <div class="data-table overflow-x-auto">
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
                        <td>
                            @if($user->is(auth()->user()))
                                <span class="badge">Admin (you)</span>
                            @else
                                <form method="POST" action="{{ route('users.role.update', $user) }}" class="flex items-center gap-2" data-role-autosave>
                                    @csrf
                                    @method('PATCH')
                                    <label for="role-{{ $user->id }}" class="sr-only">Role for {{ $user->name }}</label>
                                    <select id="role-{{ $user->id }}" name="role" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                        @foreach(['admin' => 'Admin', 'user' => 'User', 'engineer' => 'Engineer'] as $role => $label)
                                            <option value="{{ $role }}" @selected($user->role === $role)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <span data-role-status role="status" aria-live="polite" class="text-sm text-gray-600"></span>
                                </form>
                            @endif
                        </td>
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
    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection

@push('scripts')
    <script defer src="{{ asset('js/user-roles.js') }}"></script>
@endpush
