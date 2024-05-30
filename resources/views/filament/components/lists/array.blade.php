<div>
  <ol class="list-inside list-disc space-y-2">
    @forelse ($array as $item)
      <li>{{ $item }}</li>
    @empty
      <div>
        <p class="text-red-500">! Invalid data</p>
      </div>
    @endforelse
  </ol>
</div>
