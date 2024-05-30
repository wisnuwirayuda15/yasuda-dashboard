@php($color = isset($color) ? $color : 'primary')

<div class="w-max">
  <x-filament::badge @class(['!text-2xl' => isset($big)]) color="{{ $color }}">{{ $text }}</x-filament::badge>
</div>
