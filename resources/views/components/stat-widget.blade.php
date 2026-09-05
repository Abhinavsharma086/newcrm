@props(['icon', 'title', 'value', 'color' => 'red'])

<div class="stat-card">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <p class="mb-2">{{ $title }}</p>
            <h3>{{ $value }}</h3>
        </div>
        <div class="icon-box {{ $color }}">
            <i class="{{ $icon }}"></i>
        </div>
    </div>
</div>
