@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    <div class="content-wrapper">
        <div class="greeting-section">
            <h1 class="greeting-title">Settings</h1>
            <p class="greeting-subtitle">Manage your account preferences</p>
        </div>

        @if(session('success'))
            <div role="status" class="mb-6 rounded-lg border border-green-300 bg-green-50 p-4 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div role="alert" class="mb-6 rounded-lg border border-red-300 bg-red-50 p-4 text-red-800">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div role="alert" class="mb-6 rounded-lg border border-red-300 bg-red-50 p-4 text-red-800">
                <p class="font-semibold">{{ $errors->hasAny(['current_password', 'password', 'password_confirmation']) ? 'Password was not changed.' : 'Your changes could not be saved.' }}</p>
                <ul class="mt-2 list-inside list-disc">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="table-container">
                <div class="table-header">
                    <h2 class="table-title">Profile Settings</h2>
                </div>
                <div style="padding: 1.5rem;">
                    <div class="modal-form-group">
                        <label for="profile-name" class="modal-label">Name</label>
                        <input id="profile-name" type="text" name="name" value="{{ Auth::user()->name }}" class="modal-input disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500" disabled>
                    </div>
                    <div class="modal-form-group">
                        <label for="profile-email" class="modal-label">Email</label>
                        <input id="profile-email" type="email" name="email" value="{{ Auth::user()->email }}" class="modal-input disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500" disabled>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2 class="table-title">Change Password</h2>
                </div>
                <div style="padding: 1.5rem;">
                    <form method="POST" action="{{ route('settings.password') }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-form-group">
                            <label class="modal-label">Current Password</label>
                            <input type="password" name="current_password" class="modal-input">
                        </div>
                        <div class="modal-form-group">
                            <label class="modal-label">New Password</label>
                            <input type="password" name="password" class="modal-input">
                        </div>
                        <div class="modal-form-group">
                            <label class="modal-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="modal-input">
                        </div>
                        <button type="submit" class="btn-create">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
