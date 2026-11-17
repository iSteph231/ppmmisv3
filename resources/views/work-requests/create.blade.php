@extends('layouts.app')

@section('title', 'Create Work Request')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Create Work Request</h1>
        <p class="greeting-subtitle">Submit a new maintenance request</p>
    </div>

    <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        @if(session('error'))
            <div role="alert" class="m-6 rounded-lg border border-red-300 bg-red-50 p-4 text-red-800">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div role="alert" class="m-6 rounded-lg border border-red-300 bg-red-50 p-4 text-red-800">
                <p>Please correct the following before submitting your request:</p>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <p>Please select your image again before resubmitting.</p>
            </div>
        @endif
        <form method="POST" action="{{ route('work-requests.store') }}" enctype="multipart/form-data" style="padding: 1.5rem;" id="workRequestForm" data-prevent-duplicate-submit>
            @csrf
            <input type="hidden" name="submission_token" value="{{ old('submission_token', (string) \Illuminate\Support\Str::uuid()) }}">

            {{-- Hidden title field --}}
            <input type="hidden" name="title" id="title" value="{{ old('title') }}">

            {{-- Department (REQUIRED) --}}
            <div style="margin-bottom: 1.5rem;">
                <label for="department" style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                    Department <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" name="department" id="department" class="search-input" style="width: 100%;" placeholder="e.g., Engineering, Registrar" value="{{ old('department') }}" required>
                @error('department')
                    <p style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Building Name (REQUIRED) --}}
            <div style="margin-bottom: 1.5rem;">
                <label for="building_name" style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                    Building Name <span style="color: #ef4444;">*</span>
                </label>
                <select name="building_name" id="building_name" class="search-input" style="width: 100%;" required>
                    <option value="">Select a building</option>
                    @foreach (['BIT', 'BSBA', 'Central Admin Building/FVR', 'Agri-Business', 'Convention Hall/Court', 'Canteen', 'Student Service', 'Dorm', 'ROTC Bldg'] as $building)
                        <option value="{{ $building }}" @selected(old('building_name') === $building)>{{ $building === 'Central Admin Building/FVR' ? $building : ($building === 'ROTC Bldg' ? 'ROTC Building' : $building.' Building') }}</option>
                    @endforeach
                </select>
                @error('building_name')
                    <p style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Name of Office / Room (REQUIRED) --}}
            @php
                $roomsByBuilding = [
                    'BIT' => ['Room 19 (Automotive)', 'Room 20 (Mechanical)', 'Room 21 (Electronics)', 'Room 22 (Electronics)', 'Room 23 (Electrical)', 'Room 24 (Electrical)', 'Comfort Room 1', 'Comfort Room 2', 'Comfort Room 3 (infront of Main Gate)'],
                    'BSBA' => ['Room 18A', 'Room 18B', 'Comfort Room 1', 'Comfort room 2'],
                    'Canteen' => ['Canteen'],
                    'Convention Hall/Court' => [
                        'Covered Court 1 (Parking Lot)',
                        'Covered Court 2 (Front of Dormitory)',
                        'Covered Court 3 (Back of the Campus)',
                    ],
                    'ROTC Bldg' => ['ROTC Headquarters'],
                    'Dorm' => [
                        'Dorm Manager Room',
                        'Dorm Room 1',
                        'Dorm Room 2',
                        'Dorm Room 3',
                        'Dorm Room 4',
                        'Dorm Room 5',
                        'Dorm Kitchen',
                    ],
                    'Student Service' => [
                        'Student Services Office',
                        'Sports / Culture',
                        'Practice Teaching',
                        'Students and Alumni Services',
                    ],
                    'Central Admin Building/FVR' => [
                        'Room 1',
                        'Room 2',
                        'Room 3',
                        'Room 4',
                        'Room 5',
                        'Room 6 (Speech Lab)',
                        'MIS',
                        'Room 8',
                        'Room 9',
                        'CED Office',
                        "Dean's Office",
                        "Administrative Officer's Office",
                        "Cashier's Office",
                        'Accounting Office',
                        'Supply Office',
                        'Planning',
                        'Stock Room',
                        'Guidance Office',
                        'Faculty Room',
                        'Campus Clinic',
                        'Campus Public Information Office',
                        'Activity Center',
                        'Accre',
                        'ETEEAP',
                    ],
                    'Agri-Business' => [
                        'Room 10 (TLE Garments)',
                        'Room 11 (Science Lab)',
                        'Room 12 (Food)',
                        'Room 13 (Food)',
                        'Room 14',
                        'Room 15',
                        'Room 16 A (Computer Lab)',
                        'Room 16 B (Computer Lab)',
                        'Room 17',
                        'Research Office',
                        'Extension / GAD Office',
                        'Campus Library',
                        "Registrar's Office",
                        'Food Tech new (BTLED)',
                        'Comfort Room',
                    ],
                ];
                $hasRoomChoices = isset($roomsByBuilding[old('building_name', '')]);
            @endphp
            <div style="margin-bottom: 1.5rem;">
                <label for="office_room_select" style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                    Name of Office / Room <span style="color: #ef4444;">*</span>
                </label>
                <select name="office_room" id="office_room_select" class="search-input" style="width: 100%;" required @disabled(! $hasRoomChoices)>
                    <option value="">{{ $hasRoomChoices ? 'Select an office / room' : 'Select a building first' }}</option>
                    @foreach ($roomsByBuilding[old('building_name', '')] ?? [] as $room)
                        <option value="{{ $room }}" @selected(old('office_room') === $room)>{{ $room }}</option>
                    @endforeach
                </select>
                @error('office_room')
                    <p style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Work Request Type - Horizontal layout --}}
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.75rem;">
                    Work Request Type <span style="color: #ef4444;">*</span>
                </label>
                
                {{-- Horizontal flex layout for radio options --}}
                <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; align-items: flex-start; margin-bottom: 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="radio" name="request_type" value="ocular_inspection" class="request-radio" {{ old('request_type') == 'ocular_inspection' ? 'checked' : '' }} required>
                        <span>Ocular inspection of:</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="radio" name="request_type" value="installation" class="request-radio" {{ old('request_type') == 'installation' ? 'checked' : '' }}>
                        <span>Installation of:</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="radio" name="request_type" value="repair" class="request-radio" {{ old('request_type') == 'repair' ? 'checked' : '' }}>
                        <span>Repair of:</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="radio" name="request_type" value="replacement" class="request-radio" {{ old('request_type') == 'replacement' ? 'checked' : '' }}>
                        <span>Replacement of:</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="radio" name="request_type" value="others" class="request-radio" {{ old('request_type') == 'others' ? 'checked' : '' }}>
                        <span>Others (specify):</span>
                    </label>
                </div>

                {{-- Detail input fields - shown conditionally based on selection --}}
                <div id="detailInputsContainer" style="margin-top: 0.75rem;">
                    <div id="ocular_detail" class="detail-field" style="display: {{ old('request_type') == 'ocular_inspection' ? 'block' : 'none' }}; margin-bottom: 0.5rem;">
                        <input type="text" name="ocular_location" class="search-input" style="width: 100%;" placeholder="Specify location / item" value="{{ old('ocular_location') }}">
                    </div>
                    <div id="installation_detail" class="detail-field" style="display: {{ old('request_type') == 'installation' ? 'block' : 'none' }}; margin-bottom: 0.5rem;">
                        <input type="text" name="installation_item" class="search-input" style="width: 100%;" placeholder="e.g., Aircon" value="{{ old('installation_item') }}">
                    </div>
                    <div id="repair_detail" class="detail-field" style="display: {{ old('request_type') == 'repair' ? 'block' : 'none' }}; margin-bottom: 0.5rem;">
                        <input type="text" name="repair_item" class="search-input" style="width: 100%;" placeholder="e.g., Ceiling leak, electrical system" value="{{ old('repair_item') }}">
                    </div>
                    <div id="replacement_detail" class="detail-field" style="display: {{ old('request_type') == 'replacement' ? 'block' : 'none' }}; margin-bottom: 0.5rem;">
                        <input type="text" name="replacement_item" class="search-input" style="width: 100%;" placeholder="e.g., Bulbs, filters, parts" value="{{ old('replacement_item') }}">
                    </div>
                    <div id="others_detail" class="detail-field" style="display: {{ old('request_type') == 'others' ? 'block' : 'none' }}; margin-bottom: 0.5rem;">
                        <input type="text" name="others_specify" class="search-input" style="width: 100%;" placeholder="Describe other request type" value="{{ old('others_specify') }}">
                    </div>
                </div>

                <div id="workTypeError" style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: none;">Please select a work request type</div>
                @error('request_type')
                    <p style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="photo">Image (required) <span style="color: #dc2626;" aria-hidden="true">*</span></label>
                <input type="file" name="photo" id="photo" class="search-input" accept="image/jpeg,image/png,image/webp" aria-describedby="photo-help photo-status" required>
                <input type="file" id="camera-photo" class="search-input" accept="image/jpeg,image/png,image/webp" capture="environment" aria-label="Take a photo" aria-describedby="photo-help photo-status" hidden>
                <div id="photo-source-buttons" hidden>
                    <div class="flex flex-wrap gap-3 mt-2">
                        <button type="button" id="take-photo" class="btn-create">Take Photo</button>
                        <button type="button" id="choose-photo" class="btn-create">Choose from Gallery</button>
                    </div>
                </div>
                <p id="photo-help">Attach an image of the issue. JPG, PNG, or WebP, up to 5 MB.</p>
                <p id="photo-status" role="status" aria-live="polite">No image selected.</p>
                <p id="photo-error" role="alert" hidden>Please take a photo or choose an image from your gallery.</p>
                @error('photo')
                    <p role="alert">{{ $message }}</p>
                @enderror
            </div>

            {{-- Form Buttons --}}
            <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                <a href="{{ route('work-requests.index') }}" class="btn-create" style="background: #9ca3af; text-decoration: none;">
                    Cancel
                </a>
                <button type="submit" class="btn-create" style="background: #3b82f6; border: none; cursor: pointer;">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/work-request-photo.js') }}" defer></script>
<script>
    const buildingSelect = document.getElementById('building_name');
    const officeRoomSelect = document.getElementById('office_room_select');
    const roomsByBuilding = @json($roomsByBuilding);

    buildingSelect.addEventListener('change', function () {
        const rooms = roomsByBuilding[buildingSelect.value] || [];
        const hasRoomChoices = rooms.length > 0;

        officeRoomSelect.replaceChildren(new Option(hasRoomChoices ? 'Select an office / room' : 'Select a building first', ''));
        rooms.forEach(room => officeRoomSelect.add(new Option(room, room)));
        officeRoomSelect.disabled = !hasRoomChoices;
    });

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
        } else {
            titleInput.value = '';
        }
    }
    
    const form = document.getElementById('workRequestForm');
    const workTypeError = document.getElementById('workTypeError');
    
    form.addEventListener('submit', function(e) {
        let radioSelected = false;
        let selectedValue = null;
        
        for (const radio of radios) {
            if (radio.checked) {
                radioSelected = true;
                selectedValue = radio.value;
                break;
            }
        }
        
        if (!radioSelected) {
            e.preventDefault();
            workTypeError.style.display = 'block';
            workTypeError.textContent = 'Please select a work request type';
            return;
        } else {
            workTypeError.style.display = 'none';
        }
        
        let detailInput = null;
        
        if (selectedValue === 'ocular_inspection') {
            detailInput = document.querySelector('input[name="ocular_location"]');
        } else if (selectedValue === 'installation') {
            detailInput = document.querySelector('input[name="installation_item"]');
        } else if (selectedValue === 'repair') {
            detailInput = document.querySelector('input[name="repair_item"]');
        } else if (selectedValue === 'replacement') {
            detailInput = document.querySelector('input[name="replacement_item"]');
        } else if (selectedValue === 'others') {
            detailInput = document.querySelector('input[name="others_specify"]');
        }
        
        if (detailInput && (!detailInput.value || detailInput.value.trim() === '')) {
            e.preventDefault();
            detailInput.style.borderColor = '#ef4444';
            detailInput.focus();
            alert('Please provide details for the selected work request type.');
            return;
        }
        
        if (detailInput) {
            detailInput.style.borderColor = '';
        }
    });
    
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            toggleDetailFields();
            updateTitle();
            workTypeError.style.display = 'none';
            document.querySelectorAll('.detail-field input').forEach(input => {
                input.style.borderColor = '';
            });
        });
    });
    
    const detailInputs = document.querySelectorAll('.detail-field input');
    detailInputs.forEach(input => {
        input.addEventListener('input', updateTitle);
        input.addEventListener('input', function() {
            this.style.borderColor = '';
        });
    });
    
    toggleDetailFields();
    updateTitle();
</script>
@endpush
@endsection
