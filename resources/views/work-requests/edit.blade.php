@extends('layouts.app')

@section('title', 'Edit Work Request')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Edit Work Request</h1>
        <p class="greeting-subtitle">Update your request details</p>
    </div>

    <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <form method="POST" action="{{ route('work-requests.update', $workRequest->id) }}" style="padding: 1.5rem;" id="workRequestForm">
            @csrf
            @method('PUT')

            <input type="hidden" name="title" id="title" value="{{ old('title', $workRequest->title) }}">

            <div style="background: #f9fafb; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                    <div>
                        <strong>Request #:</strong> {{ $workRequest->request_number ?? 'N/A' }}
                    </div>
                    <div>
                        <strong>Status:</strong> {{ ucfirst($workRequest->status) }}
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="department" style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                    Department
                </label>
                <input type="text" name="department" id="department" class="search-input" style="width: 100%;" placeholder="e.g., Engineering, Registrar" value="{{ old('department', $workRequest->department) }}">
                @error('department')
                    <p style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="building_name" style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                    Building Name
                </label>
                <input type="text" name="building_name" id="building_name" class="search-input" style="width: 100%;" placeholder="e.g., Admin Building, Science Hall" value="{{ old('building_name', $workRequest->building_name) }}">
                @error('building_name')
                    <p style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="office_room" style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                    Name of Office / Room
                </label>
                <input type="text" name="office_room" id="office_room" class="search-input" style="width: 100%;" placeholder="Room #, Office name" value="{{ old('office_room', $workRequest->office_room) }}">
                @error('office_room')
                    <p style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.75rem;">
                    Work Request Type <span style="color: #ef4444;">*</span>
                </label>

                <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; align-items: flex-start; margin-bottom: 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="radio" name="request_type" value="ocular_inspection" class="request-radio" {{ old('request_type', $workRequest->work_type) === 'ocular_inspection' ? 'checked' : '' }} required>
                        <span>Ocular inspection of:</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="radio" name="request_type" value="installation" class="request-radio" {{ old('request_type', $workRequest->work_type) === 'installation' ? 'checked' : '' }}>
                        <span>Installation of:</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="radio" name="request_type" value="repair" class="request-radio" {{ old('request_type', $workRequest->work_type) === 'repair' ? 'checked' : '' }}>
                        <span>Repair of:</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="radio" name="request_type" value="replacement" class="request-radio" {{ old('request_type', $workRequest->work_type) === 'replacement' ? 'checked' : '' }}>
                        <span>Replacement of:</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="radio" name="request_type" value="others" class="request-radio" {{ old('request_type', $workRequest->work_type) === 'others' ? 'checked' : '' }}>
                        <span>Others (specify):</span>
                    </label>
                </div>

                <div id="detailInputsContainer" style="margin-top: 0.75rem;">
                    <div id="ocular_detail" class="detail-field" style="display: {{ old('request_type', $workRequest->work_type) === 'ocular_inspection' ? 'block' : 'none' }}; margin-bottom: 0.5rem;">
                        <input type="text" name="ocular_location" class="search-input" style="width: 100%;" placeholder="Specify location / item" value="{{ old('ocular_location', $workRequest->ocular_details) }}">
                    </div>
                    <div id="installation_detail" class="detail-field" style="display: {{ old('request_type', $workRequest->work_type) === 'installation' ? 'block' : 'none' }}; margin-bottom: 0.5rem;">
                        <input type="text" name="installation_item" class="search-input" style="width: 100%;" placeholder="e.g., Aircon" value="{{ old('installation_item', $workRequest->installation_details) }}">
                    </div>
                    <div id="repair_detail" class="detail-field" style="display: {{ old('request_type', $workRequest->work_type) === 'repair' ? 'block' : 'none' }}; margin-bottom: 0.5rem;">
                        <input type="text" name="repair_item" class="search-input" style="width: 100%;" placeholder="e.g., Ceiling leak, electrical system" value="{{ old('repair_item', $workRequest->repair_details) }}">
                    </div>
                    <div id="replacement_detail" class="detail-field" style="display: {{ old('request_type', $workRequest->work_type) === 'replacement' ? 'block' : 'none' }}; margin-bottom: 0.5rem;">
                        <input type="text" name="replacement_item" class="search-input" style="width: 100%;" placeholder="e.g., Bulbs, filters, parts" value="{{ old('replacement_item', $workRequest->replacement_details) }}">
                    </div>
                    <div id="others_detail" class="detail-field" style="display: {{ old('request_type', $workRequest->work_type) === 'others' ? 'block' : 'none' }}; margin-bottom: 0.5rem;">
                        <input type="text" name="others_specify" class="search-input" style="width: 100%;" placeholder="Describe other request type" value="{{ old('others_specify', $workRequest->others_details) }}">
                    </div>
                </div>

                @error('request_type')
                    <p style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                <a href="{{ route('work-requests.show', $workRequest->id) }}" class="btn-create" style="background: #9ca3af; text-decoration: none;">
                    Cancel
                </a>
                <button type="submit" class="btn-create" style="background: #3b82f6; border: none; cursor: pointer;">
                    Update Request
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const titleInput = document.getElementById('title');
    const radios = document.querySelectorAll('.request-radio');
    const detailFields = document.querySelectorAll('.detail-field');

    function toggleDetailFields() {
        let selectedValue = null;

        for (const radio of radios) {
            if (radio.checked) {
                selectedValue = radio.value;
                break;
            }
        }

        detailFields.forEach(field => {
            field.style.display = 'none';
        });

        if (selectedValue === 'ocular_inspection') {
            document.getElementById('ocular_detail').style.display = 'block';
        } else if (selectedValue === 'installation') {
            document.getElementById('installation_detail').style.display = 'block';
        } else if (selectedValue === 'repair') {
            document.getElementById('repair_detail').style.display = 'block';
        } else if (selectedValue === 'replacement') {
            document.getElementById('replacement_detail').style.display = 'block';
        } else if (selectedValue === 'others') {
            document.getElementById('others_detail').style.display = 'block';
        }
    }

    function updateTitle() {
        let selectedValue = null;
        let detail = '';

        for (const radio of radios) {
            if (radio.checked) {
                selectedValue = radio.value;
                break;
            }
        }

        if (selectedValue === 'ocular_inspection') {
            const val = document.querySelector('input[name="ocular_location"]')?.value || '';
            detail = val ? `: ${val}` : '';
            titleInput.value = `Ocular inspection of${detail}`;
        } else if (selectedValue === 'installation') {
            const val = document.querySelector('input[name="installation_item"]')?.value || '';
            detail = val ? `: ${val}` : '';
            titleInput.value = `Installation of${detail}`;
        } else if (selectedValue === 'repair') {
            const val = document.querySelector('input[name="repair_item"]')?.value || '';
            detail = val ? `: ${val}` : '';
            titleInput.value = `Repair of${detail}`;
        } else if (selectedValue === 'replacement') {
            const val = document.querySelector('input[name="replacement_item"]')?.value || '';
            detail = val ? `: ${val}` : '';
            titleInput.value = `Replacement of${detail}`;
        } else if (selectedValue === 'others') {
            const val = document.querySelector('input[name="others_specify"]')?.value || '';
            detail = val ? `: ${val}` : '';
            titleInput.value = `Others${detail}`;
        }
    }

    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            toggleDetailFields();
            updateTitle();
        });
    });

    document.querySelectorAll('.detail-field input').forEach(input => {
        input.addEventListener('input', updateTitle);
    });

    toggleDetailFields();
    updateTitle();
</script>
@endpush
@endsection
