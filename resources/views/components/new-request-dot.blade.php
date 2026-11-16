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
</span>
