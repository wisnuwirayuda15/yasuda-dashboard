@php
  $color = isset($color) ? $color : 'primary';
@endphp

<div class="w-max">
  <x-filament::badge class="{{ isset($big) ? '!text-2xl' : '' }}" color="{{ $color }}">{{ $text }}</x-filament::badge>
</div>
