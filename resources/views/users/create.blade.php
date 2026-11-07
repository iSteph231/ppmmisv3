@extends('layouts.app')

@section('title', 'Add Account')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Add Account</h1>
        <p class="greeting-subtitle">Create an account and assign its role.</p>
    </div>
    <div class="rounded-xl bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('users.store') }}" class="grid gap-5">
            @csrf
            @foreach(['name' => 'Full name', 'email' => 'Email address', 'department' => 'Department (optional)'] as $field => $label)
                <div>
                    <label for="{{ $field }}" class="mb-2 block font-semibold">{{ $label }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'email' ? 'email' : 'text' }}" value="{{ old($field) }}" maxlength="255" @required(in_array($field, ['name', 'email'])) class="w-full rounded-lg border border-gray-300 px-4 py-3 text-base" @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>
                    @error($field)<p id="{{ $field }}-error" class="mt-2 text-red-700">{{ $message }}</p>@enderror
                </div>
            @endforeach
            <div>
                <label for="role" class="mb-2 block font-semibold">Role</label>
                <select id="role" name="role" required class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-base">
                    @foreach(['user' => 'User', 'engineer' => 'Engineer'] as $role => $label)
                        <option value="{{ $role }}" @selected(old('role', 'user') === $role)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('role')<p class="mt-2 text-red-700">{{ $message }}</p>@enderror
            </div>
            <p class="text-gray-600">A random password will be generated and emailed with the login details to the account email address.</p>
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-lg bg-blue-700 px-5 py-3 font-semibold text-white hover:bg-blue-800">Create account</button>
                <a href="{{ route('users.index') }}" class="rounded-lg border border-gray-400 px-5 py-3 font-semibold">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
