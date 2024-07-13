@php
  use Illuminate\Support\Str;

  $color = isset($color) ? $color : 'primary';
  // $role = Str::headline(auth()->user()->roles()->pluck('name')->implode(', '));
  $roles = auth()->user()->roles;
@endphp

<div class="w-max flex space-x-2">
  @foreach ($roles as $role)
    <x-filament::badge @class(['!text-lg' => isset($big)]) color="{{ $color }}">
      {{ Str::headline($role->name) }}
    </x-filament::badge>
  @endforeach
</div>
