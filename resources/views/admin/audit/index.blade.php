@extends('layouts.admin')
@section('title', 'Audit log')

@section('content')
    <x-ui.page-header compact title="Audit log" description="Sign-ins, publishing, lead changes, exports and settings changes. Entries cannot be edited.">
        <x-slot:eyebrow>Company</x-slot:eyebrow>
    </x-ui.page-header>

    <form method="GET" class="uh-admin-toolbar">
        <x-ui.select name="actor_id" label="Person">
            <option value="">Anyone</option>
            @foreach($actors as $actor)
                <option value="{{ $actor->id }}" @selected(($filters['actor_id'] ?? '') == $actor->id)>{{ $actor->name }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.input name="action" label="Action starts with" :value="$filters['action'] ?? ''" placeholder="lead." />
        <x-ui.input name="from" label="From" type="date" :value="$filters['from'] ?? ''" />
        <x-ui.input name="to" label="To" type="date" :value="$filters['to'] ?? ''" />
        <div class="uh-admin-toolbar-actions">
            <button type="submit" class="uh-btn-primary uh-btn-sm">Filter</button>
            @if(collect($filters)->filter()->isNotEmpty())
                <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.audit.index') }}">Clear</a>
            @endif
        </div>
    </form>

    @if($logs->isNotEmpty())
        <div class="uh-panel-flush mt-6 overflow-hidden">
            <div class="uh-table-scroll">
                <table class="uh-table">
                    <caption class="sr-only">Audit entries</caption>
                    <thead>
                        <tr>
                            <th scope="col">When</th>
                            <th scope="col">Who</th>
                            <th scope="col">Action</th>
                            <th scope="col">Subject</th>
                            <th scope="col">Change</th>
                            <th scope="col">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td class="whitespace-nowrap text-xs">{{ \App\Support\DisplayTimezone::format($log->created_at) }}</td>
                                <td class="whitespace-nowrap text-sm">{{ $log->actor?->name ?? 'System' }}</td>
                                <td class="whitespace-nowrap font-mono text-xs">{{ $log->action }}</td>
                                <td class="whitespace-nowrap text-xs">{{ $log->subject_type ? class_basename($log->subject_type).' #'.$log->subject_id : '—' }}</td>
                                <td class="max-w-md text-xs">
                                    @if($log->old_values || $log->new_values)
                                        <details>
                                            <summary class="cursor-pointer text-[var(--color-muted)]">Details</summary>
                                            <pre class="uh-admin-code mt-2 max-h-48 overflow-auto whitespace-pre-wrap break-all rounded-md p-2 text-[0.6875rem]">{{ json_encode(['before' => $log->old_values, 'after' => $log->new_values], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </details>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="whitespace-nowrap font-mono text-xs" dir="ltr">{{ $log->ip ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
            <div class="mt-6">{{ $logs->links() }}</div>
        @endif
    @else
        <x-ui.empty class="mt-6" icon="shield" title="No entries match" description="Try a wider date range or clear the filters." />
    @endif
@endsection
