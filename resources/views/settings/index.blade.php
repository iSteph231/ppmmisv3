@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Settings</h1>
        <p class="greeting-subtitle">Manage your account preferences</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">Profile Settings</h2>
            </div>
            <div style="padding: 1.5rem;">
                <form method="POST" action="{{ route('settings.profile') }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-form-group">
                        <label class="modal-label">Name</label>
                        <input type="text" name="name" value="{{ Auth::user()->name }}" class="modal-input">
                    </div>
                    <div class="modal-form-group">
                        <label class="modal-label">Email</label>
                        <input type="email" name="email" value="{{ Auth::user()->email }}" class="modal-input">
                    </div>
                    <button type="submit" class="btn-create">Update Profile</button>
                </form>
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