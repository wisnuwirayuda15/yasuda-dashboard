<span class="flex">
  <span style="margin-right: 0.5rem">{{ $record->name }}</span>
  <x-filament::badge>{{ $record->seat_set->getLabel() }}</x-filament::badge>
</span>
