<div class="mt-4 grid grid-cols-2 gap-6">
  @if ($page->getPrevious())
    <div class="relative space-y-1 p-4 rounded border border-zinc-900/10 shadow transition hover:bg-zinc-900/5">
      <div class="text-sm font-heading font-extrabold text-zinc-900/30 uppercase">Previous</div>
      <a class="font-heading font-extrabold text-zinc-900/80" href="{{ $page->getPrevious()->getPath() }}">{{ $page->getPrevious()->title }} <span class="absolute inset-0"></span></a>
    </div>
  @endif

  @if ($page->getNext())
    <div class="col-start-2 relative space-y-1 p-4 rounded border border-zinc-900/10 shadow transition hover:bg-zinc-900/5">
      <div class="text-sm font-heading font-extrabold text-zinc-900/30 uppercase">Next</div>
      <a class="font-heading font-extrabold text-zinc-900/80" href="{{ $page->getNext()->getPath() }}">{{ $page->getNext()->title }} <span class="absolute inset-0"></span></a>
    </div>
  @endif
</div>
