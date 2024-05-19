<span class="grid">
    {{ $regency }} - 
    @if (!empty($badges))
      @foreach ($badges as $badge)
        <x-filament::badge>{{ $badge }}</x-filament::badge>
      @endforeach
    @endif
</span>
