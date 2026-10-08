@extends('layouts.admin')
@section('title', 'Follow-ups')

@section('content')
    <x-ui.page-header compact title="Follow-ups" description="Open follow-ups, most urgent first. Times are shown in Dhaka time.">
        <x-slot:eyebrow>Communication</x-slot:eyebrow>
    </x-ui.page-header>

    <x-ui.admin-related label="Next to this">
        <a href="{{ route('admin.leads.index') }}">Leads</a>
        <a href="{{ route('admin.visits.index') }}">Site visits</a>
    </x-ui.admin-related>

    <x-ui.admin-tabs label="Follow-up views">
        @foreach(['overdue' => 'Overdue', 'all' => 'All open'] as $value => $label)
            <a href="{{ route('admin.follow-ups.index', ['view' => $value]) }}"
               @class(['is-active' => $view === $value])
               @if($view === $value) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </x-ui.admin-tabs>

    @if($followUps->isNotEmpty())
        <div class="uh-panel-flush overflow-hidden">
            <div class="uh-table-scroll">
                <table class="uh-table">
                    <caption class="sr-only">Open follow-ups</caption>
                    <thead>
                        <tr>
                            <th scope="col">Due</th>
                            <th scope="col">Lead</th>
                            <th scope="col">Action</th>
                            <th scope="col">Priority</th>
                            <th scope="col">Owner</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($followUps as $followUp)
                            <tr class="uh-admin-clickrow">
                                <td @class(['whitespace-nowrap text-xs', 'font-semibold text-[var(--color-danger)]' => $followUp->scheduled_at->isPast()])>
                                    {{ \App\Support\DisplayTimezone::format($followUp->scheduled_at) }}
                                </td>
                                <td>
                                    <a class="uh-admin-row-main uh-link-quiet font-medium" href="{{ route('admin.leads.show', $followUp->lead_id) }}">{{ $followUp->lead?->name }}</a>
                                    @if($followUp->lead)<span class="mt-0.5 block"><x-ui.status :status="$followUp->lead->status" /></span>@endif
                                </td>
                                <td class="text-sm">
                                    {{ ucfirst(str_replace('_', ' ', $followUp->action_type)) }}
                                    @if($followUp->notes)<span class="block text-xs text-[var(--color-muted)]">{{ \Illuminate\Support\Str::limit($followUp->notes, 80) }}</span>@endif
                                </td>
                                <td><x-ui.status :status="$followUp->priority ?? 'medium'" /></td>
                                <td class="whitespace-nowrap text-sm">{{ $followUp->user?->name ?? '—' }}</td>
                                <td class="uh-admin-row-actions">
                                    <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.leads.show', $followUp->lead_id) }}">Open lead</a>
                                    <form method="POST" action="{{ route('admin.follow-ups.complete', $followUp) }}">
                                        @csrf
                                        <button type="submit" class="uh-btn-outline uh-btn-sm">Done</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($followUps->hasPages())
            <div class="mt-6">{{ $followUps->links() }}</div>
        @endif
    @else
        <x-ui.empty icon="check-circle" title="No follow-ups due"
                    :description="$view === 'overdue' ? 'Nothing is overdue. Check all open follow-ups to plan ahead.' : 'Schedule follow-ups from a lead to see them here.'">
            <a class="uh-btn-outline uh-btn-sm" href="{{ route('admin.leads.index') }}">Open leads</a>
        </x-ui.empty>
    @endif
@endsection
