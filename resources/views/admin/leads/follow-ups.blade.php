@extends('layouts.admin')
@section('title', 'Follow-ups')

@section('content')
    <x-ui.page-header compact title="Follow-ups" description="Open follow-ups, most urgent first. Times are shown in Dhaka time." />

    <nav class="mt-5 flex flex-wrap gap-2" aria-label="Follow-up views">
        @foreach(['overdue' => 'Overdue', 'all' => 'All open'] as $value => $label)
            <a href="{{ route('admin.follow-ups.index', ['view' => $value]) }}"
               @class(['uh-btn-sm', 'uh-btn-primary' => $view === $value, 'uh-btn-outline' => $view !== $value])
               @if($view === $value) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>

    @if($followUps->isNotEmpty())
        <div class="uh-panel-flush mt-5 overflow-hidden">
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
                            <tr>
                                <td @class(['whitespace-nowrap text-xs', 'font-semibold text-[var(--color-danger)]' => $followUp->scheduled_at->isPast()])>
                                    {{ \App\Support\DisplayTimezone::format($followUp->scheduled_at) }}
                                </td>
                                <td>
                                    <a class="uh-link-quiet font-medium" href="{{ route('admin.leads.show', $followUp->lead_id) }}">{{ $followUp->lead?->name }}</a>
                                    @if($followUp->lead)<span class="mt-0.5 block"><x-ui.status :status="$followUp->lead->status" /></span>@endif
                                </td>
                                <td class="text-sm">
                                    {{ ucfirst(str_replace('_', ' ', $followUp->action_type)) }}
                                    @if($followUp->notes)<span class="block text-xs text-[var(--color-muted)]">{{ \Illuminate\Support\Str::limit($followUp->notes, 80) }}</span>@endif
                                </td>
                                <td><x-ui.status :status="$followUp->priority ?? 'medium'" /></td>
                                <td class="whitespace-nowrap text-sm">{{ $followUp->user?->name ?? '—' }}</td>
                                <td class="whitespace-nowrap text-right">
                                    <form method="POST" action="{{ route('admin.follow-ups.complete', $followUp) }}" class="inline">
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
        <x-ui.empty class="mt-6" icon="check-circle" title="No follow-ups due"
                    :description="$view === 'overdue' ? 'Nothing is overdue. Check all open follow-ups to plan ahead.' : 'Schedule follow-ups from a lead to see them here.'">
            <a class="uh-btn-outline uh-btn-sm" href="{{ route('admin.leads.index') }}">Open leads</a>
        </x-ui.empty>
    @endif
@endsection
