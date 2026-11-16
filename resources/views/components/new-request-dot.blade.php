<<<<<<< HEAD
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 whitespace-nowrap text-xs font-semibold text-blue-700']) }} title="New request">
    <span class="inline-block h-2 w-2 shrink-0 rounded-full bg-blue-600" aria-hidden="true"></span>
    New
=======
@props(['createdAt' => null, 'eligible' => true])
@php
    $submittedAt = $createdAt ? \Illuminate\Support\Carbon::parse($createdAt) : null;
    $expiresAt = $submittedAt?->copy()->addDay();
@endphp
<span class="request-reference">
@if($eligible && auth()->user()?->isAdmin() && $submittedAt && $submittedAt->lessThanOrEqualTo(now()) && $expiresAt->greaterThan(now()))
    <span class="new-request-indicator" data-new-request-expires="{{ $expiresAt->getTimestampMs() }}" title="New request — submitted within the last 24 hours">
        <span class="new-request-label">New</span>
        <span class="new-request-dot" aria-hidden="true"><span class="new-request-ring"></span></span>
    </span>
@endif
    <span class="request-reference-value">{{ $slot }}</span>
>>>>>>> c8949c828c63eb0a3ac103debc06788488f8fb30
</span>
