@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
    <x-ui.page-header compact title="Admin dashboard"
                      description="Leads, visits and listings that need a decision today." />

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        @foreach([
            ['label' => 'New leads', 'value' => $newLeads, 'icon' => 'inbox', 'url' => route('admin.leads.index', ['status' => 'new']), 'action' => 'Open leads'],
            ['label' => 'Visits ahead', 'value' => $visits, 'icon' => 'calendar', 'url' => route('admin.visits.index'), 'action' => 'Open visits'],
            ['label' => 'Pending review', 'value' => $pendingReview, 'icon' => 'document', 'url' => route('admin.properties.index'), 'action' => 'Open properties'],
        ] as $stat)
            <div class="uh-panel flex flex-col">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-[var(--color-muted)]">{{ $stat['label'] }}</p>
                    <span class="flex size-8 items-center justify-center rounded-lg bg-sand text-[var(--color-gold-ink)]">
                        <x-icon :name="$stat['icon']" class="size-4" />
                    </span>
                </div>
                <p class="uh-numeric mt-3 text-4xl font-semibold leading-none tracking-tight">{{ $stat['value'] }}</p>
                <a class="uh-link mt-4 text-xs" href="{{ $stat['url'] }}">{{ $stat['action'] }}</a>
            </div>
        @endforeach
    </div>

    <section class="mt-9" aria-labelledby="recent-leads">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 id="recent-leads" class="uh-h3">Recent leads</h2>
            <a class="uh-btn-outline uh-btn-sm" href="{{ route('admin.leads.index') }}">All leads</a>
        </div>

        @if($recentLeads->isNotEmpty())
            <div class="uh-panel-flush mt-4 overflow-hidden">
                <div class="uh-table-scroll">
                    <table class="uh-table">
                        <caption class="sr-only">The eight most recent leads for your desk</caption>
                        <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Property</th>
                                <th scope="col">Status</th>
                                <th scope="col">Assigned</th>
                                <th scope="col">Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentLeads as $lead)
                                <tr>
                                    <td class="font-medium">
                                        <a class="uh-link-quiet" href="{{ route('admin.leads.show', $lead) }}">{{ $lead->name }}</a>
                                        <span class="mt-0.5 block text-xs font-normal text-[var(--color-muted)]" dir="ltr">{{ $lead->phone }}</span>
                                    </td>
                                    <td class="min-w-44 max-w-56 truncate">{{ $lead->property?->title ?? '—' }}</td>
                                    <td><x-ui.status :status="$lead->status" /></td>
                                    <td class="whitespace-nowrap">{{ $lead->assignee?->name ?? 'Unassigned' }}</td>
                                    <td class="whitespace-nowrap text-xs text-[var(--color-muted)]">
                                        {{ \App\Support\DisplayTimezone::format($lead->created_at) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <x-ui.empty class="mt-4" icon="inbox" title="No leads yet"
                        description="Enquiries submitted on the public site will land here straight away." />
        @endif
    </section>
@endsection
