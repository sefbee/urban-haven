<section class="uh-panel" aria-labelledby="publication-heading">
    <h2 id="publication-heading" class="uh-h4">Publication</h2>
    <p class="mt-2 flex items-center gap-2 text-sm"><x-ui.status :status="$status" /></p>

    @if($checklist !== [])
        <div class="uh-admin-callout mt-4 rounded-lg p-3 text-xs">
            <p class="font-semibold">Before publishing, add:</p>
            <ul class="mt-1.5 list-disc space-y-0.5 pl-4">
                @foreach($checklist as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </div>
    @else
        <p class="mt-3 text-xs text-[var(--color-muted)]">All publishing requirements are met.</p>
    @endif

    @if($model->publicationState?->review_note && $status === \App\Models\PublicationState::DRAFT)
        <p class="mt-3 rounded-lg border border-line p-3 text-xs"><span class="font-semibold">Reviewer note:</span> {{ $model->publicationState->review_note }}</p>
    @endif

    <div class="mt-4 space-y-2">
        @can('submit', $model)
            <form method="POST" action="{{ route($routePrefix.'.submit', $model) }}">
                @csrf
                <button type="submit" class="uh-btn-outline uh-btn-sm uh-btn-block">Submit for review</button>
            </form>
        @endcan
        @can('publish', $model)
            @if($status === \App\Models\PublicationState::PENDING_REVIEW)
                <form method="POST" action="{{ route($routePrefix.'.approve', $model) }}">
                    @csrf
                    <button type="submit" class="uh-btn-outline uh-btn-sm uh-btn-block">Approve</button>
                </form>
                <form method="POST" action="{{ route($routePrefix.'.return', $model) }}" class="space-y-2">
                    @csrf
                    <x-ui.input name="review_note" label="Return with a note" required maxlength="500" />
                    <button type="submit" class="uh-btn-ghost uh-btn-sm uh-btn-block">Return to draft</button>
                </form>
            @endif
            @if($status !== \App\Models\PublicationState::PUBLISHED)
                <form method="POST" action="{{ route($routePrefix.'.publish', $model) }}">
                    @csrf
                    <button type="submit" class="uh-btn-primary uh-btn-sm uh-btn-block" @disabled($checklist !== [])>Publish</button>
                </form>
            @else
                <form method="POST" action="{{ route($routePrefix.'.unpublish', $model) }}"
                      class="space-y-2 border-t border-line pt-4"
                      x-data="uhConfirm('Unpublish this {{ $noun }}? It will disappear from the public site immediately.')">
                    @csrf
                    <x-ui.input name="unpublish_reason" label="Reason for unpublishing" required maxlength="255" hint="Recorded in the audit log." />
                    <button type="submit" class="uh-btn-danger uh-btn-sm uh-btn-block" @click="confirm($event)">Unpublish</button>
                </form>
            @endif
        @else
            <p class="text-xs text-[var(--color-muted)]">A publisher approves and publishes {{ $noun }}s.</p>
        @endcan
    </div>
</section>
