@props(['title' => null])

<div class="card">
    @if($title)
    <div class="card-header">
        {{ $title }}
    </div>
    @endif
    <div class="card-body">
        {{ $slot }}
    </div>
</div>
