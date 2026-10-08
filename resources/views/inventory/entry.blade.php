@extends('layouts.app')

@section('title', 'Inventory - '.$name)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/inventory.css') }}">
@endpush

@section('content')
<div class="content-wrapper inventory-page"><div class="mx-auto w-full max-w-6xl">
    <div class="greeting-section">
        <h1 class="greeting-title">{{ $name }}</h1>
        <p class="greeting-subtitle">Add inventory details and inspection records.</p>
    </div>
    <a href="{{ route('inventory.index') }}" class="mb-6 inline-block text-blue-600 hover:underline">Back to Inventory</a>

    @if (session('success'))
        <div role="status" class="mb-6 rounded-lg bg-green-50 p-4 text-green-800">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div role="alert" class="inventory-errors">
            <strong>Please complete the highlighted fields.</strong>
            <ul>@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('inventory.entries.store', $form) }}" class="inventory-entry-form mb-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @csrf
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 bg-slate-50 px-5 py-5 sm:px-8">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-slate-900">New {{ $name }} Entry</h2>
                <p class="text-sm text-slate-500">Enter the item details, then complete both inspections below. Dates and maintenance actions are optional.</p>
            </div>
            <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-500">* Required fields</span>
        </div>
        <div class="inventory-sections p-5 sm:p-8">
        @foreach ($sections as $section => $fields)
            <fieldset class="inventory-section {{ $loop->first ? 'inventory-item-section' : 'inventory-inspection-section' }} min-w-0 rounded-xl border border-slate-200 p-4 sm:p-6">
                <legend class="flex items-center gap-3 px-2 text-sm font-semibold text-slate-800">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-700">{{ $loop->iteration }}</span>
                    {{ $section }}
                </legend>
                <div class="inventory-fields grid grid-cols-1 gap-x-5 gap-y-5 pt-2 sm:grid-cols-2">
                    @foreach ($fields as $key => $field)
                        @if ($field['type'] !== 'checkbox')
                            <div class="min-w-0 space-y-2">
                                <label for="{{ $key }}" class="block text-sm font-medium text-slate-700">
                                    {{ $field['label'] }}@if ($field['required']) <span class="text-blue-600">*</span>@endif
                                </label>
                                <input id="{{ $key }}" name="data[{{ $key }}]" type="{{ $field['type'] }}"
                                    value="{{ old('data.'.$key) }}" @required($field['required'])
                                    @if ($field['type'] === 'number') min="0.01" max="10000" step="0.01" @endif
                                    @if ($field['type'] === 'text') maxlength="255" placeholder="Enter {{ strtolower($field['label']) }}" @endif
                                    @error('data.'.$key) aria-invalid="true" aria-describedby="{{ $key }}-error" @enderror
                                    class="block h-11 w-full min-w-0 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 @error('data.'.$key) border-red-400 @enderror">
                                @error('data.'.$key)
                                    <p id="{{ $key }}-error" class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    @endforeach
                </div>
                @unless ($loop->first)
                    <div class="inventory-maintenance mt-5 space-y-3 border-t border-slate-100 pt-5">
                        <div>
                            <h3 class="text-sm font-medium text-slate-700">Maintenance performed</h3>
                            <p class="mt-1 text-xs text-slate-400">Select all actions completed during this inspection.</p>
                        </div>
                        <div class="inventory-actions grid grid-cols-1 gap-3 sm:grid-cols-3">
                            @foreach ($fields as $key => $field)
                                @if ($field['type'] === 'checkbox')
                                    <div>
                                        <input type="hidden" name="data[{{ $key }}]" value="0">
                                        <label for="{{ $key }}" class="flex min-h-11 cursor-pointer items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 has-checked:border-blue-300 has-checked:bg-blue-50 has-checked:text-blue-700">
                                            <input id="{{ $key }}" type="checkbox" name="data[{{ $key }}]" value="1" @checked(old('data.'.$key))
                                                @error('data.'.$key) aria-invalid="true" aria-describedby="{{ $key }}-error" @enderror
                                                class="h-4 w-4 shrink-0 rounded border-slate-300 accent-blue-600 focus:ring-2 focus:ring-blue-200">
                                            {{ $field['label'] }}
                                        </label>
                                        @error('data.'.$key)
                                            <p id="{{ $key }}-error" class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endunless
            </fieldset>
        @endforeach
        </div>
        @error('data')
            <p role="alert" class="text-sm text-red-600">{{ $message }}</p>
        @enderror
        <div class="inventory-form-footer flex flex-col gap-4 border-t border-slate-200 bg-slate-50 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <p class="text-xs text-slate-500">Review the item details before saving.</p>
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('inventory.index') }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100">Cancel</a>
                <button type="submit" class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Save Entry</button>
            </div>
        </div>
    </form>

    <div class="inventory-records table-container">
        <div class="table-header">
            <div><h2 class="table-title">Saved {{ $name }} Entries</h2><p class="inventory-record-count">{{ $entries->total() }} {{ $entries->total() === 1 ? 'record' : 'records' }} ? Select an entry to view all details</p></div>
            <a href="{{ route('inventory.export-pdf', $form) }}" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">Export All PDF</a>
        </div>
        <div class="inventory-record-list space-y-4 p-6">
            @forelse ($entries as $entry)
                <details class="inventory-record group overflow-hidden rounded-xl border border-slate-200 bg-white p-5">
                    <summary class="cursor-pointer text-sm font-semibold text-slate-800">
                        {{ $entry->data['item_number'] }} — {{ $entry->data['building_name'] }}, {{ $entry->data['room'] }}
                        <span class="mt-1 block text-xs font-normal text-slate-400 sm:mt-1">Saved {{ $entry->created_at->format('M d, Y H:i') }}</span>
                    </summary>
                    <div class="mt-4 flex justify-end">
                        <a href="{{ route('inventory.entries.export-pdf', ['form' => $form, 'entry' => $entry]) }}" class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-100">Export PDF</a>
                    </div>
                    @foreach ($sections as $section => $fields)
                        <h3 class="mt-5 border-t border-slate-100 pt-4 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $section }}</h3>
                        <dl class="mt-2 grid grid-cols-1 gap-3 text-sm md:grid-cols-2 lg:grid-cols-3">
                            @foreach ($fields as $key => $field)
                                <div>
                                    <dt class="text-xs text-slate-400">{{ $field['label'] }}</dt>
                                    <dd class="mt-1 break-words font-medium text-slate-700">{{ $field['type'] === 'checkbox' ? (!empty($entry->data[$key]) ? 'Yes' : 'No') : ($entry->data[$key] ?? '—') }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    @endforeach
                </details>
            @empty
                <p class="text-sm text-slate-500">No entries yet. Add the first {{ $name }} entry above.</p>
            @endforelse
            {{ $entries->links() }}
        </div>
    </div>
</div></div>
@endsection
