<div>
  <ol class="space-y-2">
    @forelse ($orderFleets as $of)
      <li class="flex items-center gap-2">
        @svg('heroicon-c-check-circle', 'h-auto w-6 text-green-500')
        <x-filament::badge class="w-max" color="success">{{ $of->code }}</x-filament::badge>
        <span>{{ $of->fleet->name }}</span>
        •
        <span>{{ $of->fleet->category->getLabel() }}</span>
        •
        <span>{{ $of->fleet->seat_set->getLabel() }}</span>
      </li>
    @empty
      <div class="flex items-center gap-2 text-red-500">
        @svg('heroicon-c-x-circle', 'h-auto w-6')
        <p class="text-sm">Tidak ada armada yang tersedia di tanggal ini.</p>
      </div>
    @endforelse
  </ol>
</div>
