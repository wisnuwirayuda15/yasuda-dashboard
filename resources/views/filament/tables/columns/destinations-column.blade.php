@php
  $x = App\Models\Destination::whereIn('id', $getState())->pluck('place')->toArray();
  $z = implode(' + ', $x);
@endphp
{{ $z }}