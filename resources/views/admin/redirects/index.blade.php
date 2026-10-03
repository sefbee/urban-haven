@extends('layouts.admin')
@section('title', 'Redirects')

@php
    $drawer = $errors->any() ? old('_drawer', 'create') : null;
@endphp

@section('content')
    <div x-data="uhAdminDrawers(@js($drawer))">
        <x-ui.page-header compact title="Redirects" description="Send old or changed URLs to their new address so search rankings and shared links keep working.">
            <x-slot:eyebrow>Company</x-slot:eyebrow>
            <x-slot:actions>
                <form method="GET" class="flex items-center gap-2">
                    <label class="sr-only" for="redirect-search">Search redirects</label>
                    <input id="redirect-search" class="uh-input min-h-9 py-1.5 text-sm" type="search" name="q" value="{{ request('q') }}" placeholder="/old-path">
                    <button type="submit" class="uh-btn-outline uh-btn-sm">Search</button>
                </form>
                <button type="button" class="uh-btn-outline uh-btn-sm" @click="open('import')">Import CSV</button>
                <button type="button" class="uh-btn-primary uh-btn-sm" @click="open('create')">
                    <x-icon name="plus" class="size-4" />
                    Add redirect
                </button>
            </x-slot:actions>
        </x-ui.page-header>

        @if(session('importFailures'))
            <x-ui.alert tone="warn" class="mb-4">
                <p class="font-semibold">Some rows were not imported:</p>
                <ul class="mt-1 list-disc pl-5 text-sm">
                    @foreach(session('importFailures') as $failure)
                        <li>{{ $failure }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <x-ui.admin-related label="Related">
            <a href="{{ route('admin.cms.index') }}">Pages</a>
            <a href="{{ route('admin.menus.index') }}">Menus</a>
        </x-ui.admin-related>

        @if($redirects->isNotEmpty())
            <div class="uh-panel-flush overflow-hidden">
                <div class="uh-table-scroll">
                    <table class="uh-table">
                        <caption class="sr-only">Redirects</caption>
                        <thead>
                            <tr>
                                <th scope="col">From</th>
                                <th scope="col">To</th>
                                <th scope="col">Type</th>
                                <th scope="col">Hits</th>
                                <th scope="col"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($redirects as $redirect)
                                <tr @class(['opacity-60' => ! $redirect->is_active])>
                                    <td class="max-w-60 break-all font-mono text-xs" dir="ltr">{{ $redirect->from_path }}</td>
                                    <td class="max-w-60 break-all font-mono text-xs" dir="ltr">{{ $redirect->to_path }}</td>
                                    <td class="whitespace-nowrap text-xs">{{ $redirect->http_code }}{{ $redirect->is_active ? '' : ' · off' }}</td>
                                    <td class="uh-numeric whitespace-nowrap text-xs">{{ number_format($redirect->hits ?? 0) }}</td>
                                    <td class="uh-admin-row-actions">
                                        <form method="POST" action="{{ route('admin.redirects.toggle', $redirect) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="uh-btn-ghost uh-btn-sm">{{ $redirect->is_active ? 'Turn off' : 'Turn on' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.redirects.destroy', $redirect) }}" class="inline" x-data="uhConfirm('Delete this redirect?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="uh-btn-ghost uh-btn-sm text-[var(--color-danger)]" @click="confirm($event)">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if($redirects->hasPages())
                <div class="mt-6">{{ $redirects->links() }}</div>
            @endif
        @else
            <x-ui.empty icon="arrow-right" title="No redirects" description="Changing a listing or page slug creates a redirect automatically. Add others here.">
                <button type="button" class="uh-btn-primary uh-btn-sm" @click="open('create')">Add redirect</button>
            </x-ui.empty>
        @endif

        <x-ui.admin-drawer name="create" title="Add a redirect">
            <form method="POST" action="{{ route('admin.redirects.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="_drawer" value="create">
                <x-ui.input name="from_path" label="From path" required maxlength="255" dir="ltr" placeholder="/old-page" />
                <x-ui.input name="to_path" label="To" required maxlength="255" dir="ltr" placeholder="/new-page" />
                <x-ui.select name="http_code" label="Type">
                    <option value="301">Permanent (301)</option>
                    <option value="302">Temporary (302)</option>
                </x-ui.select>
                <x-ui.input name="reason" label="Reason" optional maxlength="255" />
                <button type="submit" class="uh-btn-primary uh-btn-block">Save redirect</button>
            </form>
        </x-ui.admin-drawer>

        <x-ui.admin-drawer name="import" title="Import CSV">
            <form method="POST" action="{{ route('admin.redirects.import') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="_drawer" value="import">
                <p class="text-sm text-[var(--color-muted)]">Header row: <code>from_path,to_path,http_code,reason</code>. The last two columns are optional. Up to 2,000 rows.</p>
                <input class="uh-input py-2 text-xs" type="file" name="file" accept=".csv,text/csv" required aria-label="CSV file">
                @error('file')<p class="uh-error">{{ $message }}</p>@enderror
                <button type="submit" class="uh-btn-primary uh-btn-block">Import</button>
            </form>
        </x-ui.admin-drawer>
    </div>
@endsection
