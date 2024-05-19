<span class="flex items-center gap-2">
  <span> {{ $record->order->customer->name }}</span>
  <x-filament::badge>{{ $record->code }}</x-filament::badge>
</span>
