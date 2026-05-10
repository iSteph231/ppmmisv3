@extends('layouts.app')

@section('title', 'Edit Work Request')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Edit Work Request</h1>
        <p class="greeting-subtitle">Update request status and information</p>
    </div>

    <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 1.5rem;">
            <form method="POST" action="{{ route('work-requests.update', $workRequest->id) }}">
                @csrf
                @method('PUT')
                
                {{-- Request Info (Read-only) --}}
                <div style="background: #f9fafb; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                        <div>
                            <strong>Request #:</strong> {{ $workRequest->request_number ?? 'N/A' }}
                        </div>
                        <div>
                            <strong>Submitted By:</strong> {{ $workRequest->user->name ?? 'N/A' }}
                        </div>
                        <div>
                            <strong>Title:</strong> {{ $workRequest->title }}
                        </div>
                        <div>
                            <strong>Work Type:</strong> {{ ucfirst(str_replace('_', ' ', $workRequest->work_type ?? 'N/A')) }}
                        </div>
                    </div>
                </div>
                
                {{-- Location Information (Editable) --}}
                <div style="margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">Location Information</h3>
                    
                    <div class="modal-form-group" style="margin-bottom: 1rem;">
                        <label class="modal-label">Department</label>
                        <input type="text" name="department" class="modal-input" value="{{ old('department', $workRequest->department) }}" placeholder="e.g., Engineering, Registrar">
                    </div>
                    
                    <div class="modal-form-group" style="margin-bottom: 1rem;">
                        <label class="modal-label">Building Name</label>
                        <input type="text" name="building_name" class="modal-input" value="{{ old('building_name', $workRequest->building_name) }}" placeholder="e.g., Admin Building, Science Hall">
                    </div>
                    
                    <div class="modal-form-group" style="margin-bottom: 1rem;">
                        <label class="modal-label">Name of Office / Room</label>
                        <input type="text" name="office_room" class="modal-input" value="{{ old('office_room', $workRequest->office_room) }}" placeholder="Room #, Office name">
                    </div>
                </div>
                
                {{-- Status Update --}}
                <div class="modal-form-group">
                    <label class="modal-label">Status</label>
                    <select name="status" class="modal-select" required>
                        <option value="pending" {{ $workRequest->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ $workRequest->status == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="completed" {{ $workRequest->status == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                
                {{-- Admin Notes --}}
                <div class="modal-form-group">
                    <label class="modal-label">Admin Notes</label>
                    <textarea name="admin_notes" rows="4" class="modal-textarea" placeholder="Add notes about this request...">{{ $workRequest->admin_notes }}</textarea>
                </div>
                
                {{-- Action Buttons --}}
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn-create">Update Request</button>
                    <a href="{{ route('work-requests.show', $workRequest->id) }}" class="btn-cancel" style="display: inline-flex; align-items: center; padding: 0.5rem 1rem; text-decoration: none;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection