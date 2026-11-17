@props(['action'])

<form method="GET" action="{{ $action }}" {{ $attributes->class(['filter-bar']) }}>
    {{ $slot }}
    @isset($help)
        <p class="filter-bar-help">{{ $help }}</p>
    @endisset
    <div class="filter-bar-actions">
        <button type="submit" class="btn-create">Apply Filters</button>
        <a href="{{ $action }}" class="filter-bar-reset">Reset</a>
        {{ $actions ?? '' }}
    </div>
</form>
