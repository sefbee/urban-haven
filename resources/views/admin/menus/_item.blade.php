<li @class(['dd-menu-item', 'is-hidden' => ! $item->is_visible]) x-data="{ editing: false }">
    <div class="dd-menu-row">
        <div class="min-w-0 flex-1">
            <p class="flex flex-wrap items-center gap-2 font-medium">
                @if($depth > 0)<x-icon name="chevron-right" class="size-3.5 text-[var(--dd-faint)]" />@endif
                {{ $item->label }}
                @unless($item->is_visible)<x-ui.badge>Hidden</x-ui.badge>@endunless
                @if($item->opens_new_tab)<x-ui.badge tone="info">New tab</x-ui.badge>@endif
            </p>
            <p class="mt-0.5 truncate text-xs text-[var(--color-muted)]" dir="ltr">{{ $item->url }}</p>
        </div>
        <div class="dd-menu-actions">
            <form method="POST" action="{{ route('admin.menus.move', $item) }}">
                @csrf
                <input type="hidden" name="direction" value="up">
                <button type="submit" class="uh-admin-icon-btn" @disabled($siblings->first()?->is($item)) aria-label="Move {{ $item->label }} up"><x-icon name="chevron-down" class="size-4 rotate-180" /></button>
            </form>
            <form method="POST" action="{{ route('admin.menus.move', $item) }}">
                @csrf
                <input type="hidden" name="direction" value="down">
                <button type="submit" class="uh-admin-icon-btn" @disabled($siblings->last()?->is($item)) aria-label="Move {{ $item->label }} down"><x-icon name="chevron-down" class="size-4" /></button>
            </form>
            <form method="POST" action="{{ route('admin.menus.visibility', $item) }}">
                @csrf
                <button type="submit" class="uh-btn-ghost uh-btn-sm">{{ $item->is_visible ? 'Hide' : 'Show' }}</button>
            </form>
            <button type="button" class="uh-btn-outline uh-btn-sm" @click="editing = ! editing" :aria-expanded="editing.toString()">Edit</button>
        </div>
    </div>

    <div x-show="editing" x-cloak class="dd-menu-edit">
        <form method="POST" action="{{ route('admin.menus.update', $item) }}" class="grid items-end gap-3 sm:grid-cols-2">
            @csrf
            @method('PUT')
            <input type="hidden" name="location" value="{{ $item->location }}">
            <input type="hidden" name="sort_order" value="{{ $item->sort_order }}">
            <x-ui.input name="label" label="Label" :value="$item->label" required maxlength="60" :id="'ml-'.$item->id" />
            <x-ui.input name="url" label="Link" :value="$item->url" required maxlength="255" dir="ltr" :id="'mu-'.$item->id" />
            @if($item->children->isEmpty())
                <div class="uh-field">
                    <label class="uh-label" for="mp-{{ $item->id }}">Sits under</label>
                    <select id="mp-{{ $item->id }}" name="parent_id" class="uh-select">
                        <option value="">Top level</option>
                        @foreach($parents->get($item->location, collect())->reject(fn ($parent) => $parent->is($item)) as $parent)
                            <option value="{{ $parent->id }}" @selected($item->parent_id === $parent->id)>{{ $parent->label }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="parent_id" value="">
            @endif
            <div class="flex flex-wrap items-center gap-4 pb-2">
                <input type="hidden" name="is_visible" value="0">
                <label class="uh-check"><input type="checkbox" name="is_visible" value="1" @checked($item->is_visible)> <span>Visible</span></label>
                <label class="uh-check"><input type="checkbox" name="opens_new_tab" value="1" @checked($item->opens_new_tab)> <span>New tab</span></label>
            </div>
            <div class="flex items-center gap-2 sm:col-span-2">
                <button type="submit" class="uh-btn-primary uh-btn-sm">Save link</button>
                <button type="button" class="uh-btn-ghost uh-btn-sm" @click="editing = false">Cancel</button>
            </div>
        </form>
        <form method="POST" action="{{ route('admin.menus.destroy', $item) }}" class="mt-2" x-data="uhConfirm(@js($item->children->isNotEmpty() ? 'Remove this link and its sub-links?' : 'Remove this link?'))">
            @csrf
            @method('DELETE')
            <button type="submit" class="uh-btn-ghost uh-btn-sm px-0 text-[var(--color-danger)]" @click="confirm($event)">Remove link</button>
        </form>
    </div>
</li>
