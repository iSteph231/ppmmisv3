@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="content-wrapper users-page">
    <div class="greeting-section">
        <h1 class="greeting-title">Users</h1>
        <p class="greeting-subtitle">Choose a role, then click Save to apply changes.</p>
    </div>

    @if(session('success'))
        <div role="status" class="mb-4 rounded-lg bg-green-50 p-4 text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div role="alert" class="mb-4 rounded-lg bg-red-50 p-4 text-red-800">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="mb-4 rounded-lg bg-red-50 p-4 text-red-800">{{ $errors->first() }}</div>
    @endif

    <div class="table-container">
        <div class="table-header flex flex-wrap items-center justify-between gap-3">
            <h2 class="table-title">All Users</h2>
            <a href="{{ route('users.create') }}" class="rounded-lg bg-blue-700 px-4 py-3 font-semibold text-white hover:bg-blue-800">Add Account</a>
        </div>
        
        <div class="data-table overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th class="users-role-column">Role</th>
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
                        <td>
                            @if($user->is(auth()->user()))
                                <span class="badge">Admin (you)</span>
                            @else
                                <form method="POST" action="{{ route('users.role.update', $user) }}" class="grid items-center gap-2" data-role-form data-user-name="{{ $user->name }}" data-user-email="{{ $user->email }}">
                                    @csrf
                                    @method('PATCH')
                                    <label for="role-{{ $user->id }}" class="sr-only">Role for {{ $user->name }}</label>
                                    <select id="role-{{ $user->id }}" name="role" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                        @foreach(['admin' => 'Admin', 'user' => 'User', 'engineer' => 'Engineer'] as $role => $label)
                                            <option value="{{ $role }}" @selected($user->role === $role)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">Save<span class="sr-only"> role for {{ $user->name }}</span></button>
                                    <span data-role-status role="status" aria-live="polite" class="text-sm text-gray-600"></span>
                                </form>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $user->is_active ? 'badge-completed' : 'badge-pending' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @unless($user->is(auth()->user()))
                                <form method="POST" action="{{ route('users.destroy', $user) }}" data-remove-account data-user-name="{{ $user->name }}" data-user-email="{{ $user->email }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-700 px-3 py-2 font-semibold text-red-800 hover:bg-red-50">Remove account</button>
                                </form>
                            @endunless
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
    <div class="mt-4">{{ $users->links() }}</div>
    <dialog id="account-removal" aria-labelledby="account-removal-title" aria-describedby="account-removal-description" class="m-auto w-full max-w-lg rounded-xl bg-white p-6 text-gray-900 shadow-xl">
        <h2 id="account-removal-title" class="text-2xl font-bold">Remove account?</h2>
        <p id="account-removal-description" class="mt-2 text-lg">This permanently removes the account. Work requests and a saved copy of the requester’s details will be retained. This action cannot be undone.</p>
        <dl class="mt-5 grid gap-4 rounded-lg bg-gray-50 p-4 text-lg">
            <div><dt class="font-semibold">Name</dt><dd data-remove-name class="break-words"></dd></div>
            <div><dt class="font-semibold">Email</dt><dd data-remove-email class="break-words"></dd></div>
        </dl>
        <form method="dialog" class="mt-6 flex flex-wrap justify-end gap-3">
            <button type="submit" value="cancel" autofocus class="rounded-lg border border-gray-400 px-5 py-3 text-lg font-semibold">Cancel</button>
            <button type="submit" value="remove" class="rounded-lg bg-red-700 px-5 py-3 text-lg font-semibold text-white hover:bg-red-800">Remove account</button>
        </form>
    </dialog>
    <dialog id="role-confirmation" aria-labelledby="role-confirmation-title" aria-describedby="role-confirmation-description" class="m-auto w-full max-w-lg rounded-xl bg-white p-6 text-gray-900 shadow-xl">
        <h2 id="role-confirmation-title" class="text-2xl font-bold">Confirm role change</h2>
        <p id="role-confirmation-description" class="mt-2 text-lg">Save the new role for this account?</p>
        <dl class="mt-5 grid gap-4 rounded-lg bg-gray-50 p-4 text-lg">
            <div><dt class="font-semibold">Name</dt><dd data-confirm-name class="break-words"></dd></div>
            <div><dt class="font-semibold">Email</dt><dd data-confirm-email class="break-words"></dd></div>
            <div><dt class="font-semibold">New role</dt><dd data-confirm-role></dd></div>
        </dl>
        <form method="dialog" class="mt-6 flex flex-wrap justify-end gap-3">
            <button type="submit" value="cancel" autofocus class="rounded-lg border border-gray-400 px-5 py-3 text-lg font-semibold">Cancel</button>
            <button type="submit" value="confirm" class="rounded-lg bg-blue-700 px-5 py-3 text-lg font-semibold text-white hover:bg-blue-800">Confirm and save</button>
        </form>
    </dialog>
</div>
@endsection

@push('scripts')
    <script defer src="{{ asset('js/user-roles.js').'?v='.substr(hash_file('sha256', public_path('js/user-roles.js')), 0, 16) }}"></script>
@endpush
