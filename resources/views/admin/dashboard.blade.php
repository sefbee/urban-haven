@extends('layouts.admin')
@section('title', 'Dashboard')

@php
    $stats = [];
    if ($seesLeads) {
        $stats[] = ['label' => 'New leads', 'value' => $leadStats['new'], 'icon' => 'inbox', 'url' => route('admin.leads.index', ['status' => 'new']), 'action' => 'Open new leads'];
        $stats[] = ['label' => 'Overdue follow-ups', 'value' => $leadStats['overdue'], 'icon' => 'clock', 'url' => route('admin.follow-ups.index'), 'action' => 'Open follow-ups', 'alert' => $leadStats['overdue'] > 0];
        $stats[] = ['label' => 'Upcoming visits', 'value' => $leadStats['visits'], 'icon' => 'calendar', 'url' => route('admin.visits.index', ['view' => 'upcoming']), 'action' => 'Open visits'];
    }
    if ($canReview) {
        $stats[] = ['label' => 'Awaiting review', 'value' => $pendingReview, 'icon' => 'check-circle', 'url' => route('admin.review.index'), 'action' => 'Open review queue'];
    } elseif ($seesInventory) {
        $stats[] = ['label' => 'Draft listings', 'value' => $drafts, 'icon' => 'document', 'url' => route('admin.properties.index', ['status' => 'draft']), 'action' => 'Open drafts'];
    }
    $pipelineMax = max([(int) $stageCounts->max(), 1]);
    $sourceMax = max([(int) $sourceCounts->max(), 1]);
@endphp

@section('content')
    <x-ui.page-header compact title="Dashboard" description="What needs a decision today. Figures come straight from the live data.">
        <x-slot:eyebrow>Desk</x-slot:eyebrow>
        <x-slot:actions>
            @if($seesLeads)
                <a class="uh-btn-outline uh-btn-sm" href="{{ route('admin.leads.index', ['view' => 'open']) }}">Open leads</a>
            @endif
            @if($seesInventory && auth()->user()->hasPermission('property.create'))
                <a class="uh-btn-primary uh-btn-sm" href="{{ route('admin.properties.create') }}">
                    <x-icon name="plus" class="size-4" />
                    New property
                </a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    @if($seesLeads || $seesInventory)
        <x-ui.admin-related label="Jump to">
            @if($seesLeads)
                <a href="{{ route('admin.leads.index') }}">Leads</a>
                <a href="{{ route('admin.follow-ups.index') }}">Follow-ups</a>
                <a href="{{ route('admin.visits.index') }}">Site visits</a>
            @endif
            @if($seesInventory)
                <a href="{{ route('admin.properties.index') }}">Properties</a>
                <a href="{{ route('admin.projects.index') }}">Projects</a>
            @endif
            @if($canReview)
                <a href="{{ route('admin.review.index') }}">Review queue</a>
            @endif
        </x-ui.admin-related>
    @endif

    @if($stats !== [])
        <div class="uh-admin-kpis">
            @foreach($stats as $stat)
                <div class="uh-admin-kpi">
                    <div class="uh-admin-kpi-top">
                        <p class="text-[0.6875rem] font-medium tracking-wide text-[var(--color-muted)]">{{ $stat['label'] }}</p>
                        <span @class(['uh-admin-kpi-icon', 'is-alert' => $stat['alert'] ?? false])>
                            <x-icon :name="$stat['icon']" class="size-4" />
                        </span>
                    </div>
                    <p @class(['uh-numeric mt-3 text-[2rem] font-semibold leading-none tracking-tight', 'text-[var(--color-danger)]' => $stat['alert'] ?? false])>{{ number_format($stat['value']) }}</p>
                    <a class="uh-link mt-auto pt-4 text-xs font-medium no-underline" href="{{ $stat['url'] }}">{{ $stat['action'] }}</a>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        @if($seesLeads)
            <section class="uh-panel" aria-labelledby="pipeline-heading">
                <h2 id="pipeline-heading" class="uh-h4">Pipeline</h2>
                <dl class="mt-4 space-y-2.5 text-sm">
                    @foreach(\App\Models\Lead::STATUS_LABELS as $status => $label)
                        @php($count = $stageCounts[$status] ?? 0)
                        <div>
                            <div class="flex justify-between gap-3">
                                <dt><a class="uh-link-quiet" href="{{ route('admin.leads.index', ['status' => $status]) }}">{{ $label }}</a></dt>
                                <dd class="uh-numeric font-semibold">{{ number_format($count) }}</dd>
                            </div>
                            <div class="uh-admin-bar mt-1.5" aria-hidden="true">
                                <div class="uh-admin-bar-track">
                                    <div class="uh-admin-bar-fill" style="width: {{ $pipelineMax > 0 ? round(($count / $pipelineMax) * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </dl>
            </section>

            <section class="uh-panel" aria-labelledby="month-heading">
                <h2 id="month-heading" class="uh-h4">Last 30 days</h2>
                <dl class="mt-4 grid grid-cols-3 gap-2">
                    <div class="uh-admin-stat">
                        <dt class="text-xs text-[var(--color-muted)]">Received</dt>
                        <dd class="uh-numeric mt-1 text-xl font-semibold">{{ number_format($leadStats['received30']) }}</dd>
                    </div>
                    <div class="uh-admin-stat">
                        <dt class="text-xs text-[var(--color-muted)]">Won</dt>
                        <dd class="uh-numeric mt-1 text-xl font-semibold">{{ number_format($leadStats['won30']) }}</dd>
                    </div>
                    <div class="uh-admin-stat">
                        <dt class="text-xs text-[var(--color-muted)]">Lost</dt>
                        <dd class="uh-numeric mt-1 text-xl font-semibold">{{ number_format($leadStats['lost30']) }}</dd>
                    </div>
                </dl>
                @if($sourceCounts->isNotEmpty())
                    <p class="mt-5 text-[0.6875rem] font-medium tracking-wide text-[var(--color-muted)]">By source</p>
                    <dl class="mt-3 space-y-2.5 text-sm">
                        @foreach($sourceCounts as $channel => $total)
                            <div>
                                <div class="flex justify-between gap-3">
                                    <dt>{{ $channel === 'direct' ? 'Direct or unknown' : $channel }}</dt>
                                    <dd class="uh-numeric">{{ number_format($total) }}</dd>
                                </div>
                                <div class="uh-admin-bar mt-1.5" aria-hidden="true">
                                    <div class="uh-admin-bar-track">
                                        <div class="uh-admin-bar-fill" style="width: {{ $sourceMax > 0 ? round(($total / $sourceMax) * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </section>
        @endif

        @if($seesInventory)
            <section class="uh-panel" aria-labelledby="inventory-heading">
                <h2 id="inventory-heading" class="uh-h4">Published inventory</h2>
                <dl class="mt-4 space-y-2.5 text-sm">
                    @foreach(\App\Models\Property::AVAILABILITY_LABELS as $availability => $label)
                        <div class="flex justify-between gap-3">
                            <dt><a class="uh-link-quiet" href="{{ route('admin.properties.index', ['availability' => $availability]) }}">{{ $label }}</a></dt>
                            <dd class="uh-numeric font-semibold">{{ number_format($inventory[$availability] ?? 0) }}</dd>
                        </div>
                    @endforeach
                </dl>
                @if($expiringReservations->isNotEmpty())
                    <p class="mt-5 text-[0.6875rem] font-medium tracking-wide text-[var(--color-muted)]">Reservations ending within 3 days</p>
                    <ul class="mt-2 space-y-1 text-sm">
                        @foreach($expiringReservations as $reserved)
                            <li><a class="uh-link" href="{{ route('admin.properties.edit', $reserved) }}">{{ $reserved->reference ?? $reserved->title }}</a> <span class="text-xs text-[var(--color-muted)]">· {{ \App\Support\DisplayTimezone::format($reserved->reservation_expires_at) }}</span></li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endif
    </div>

    @if($seesLeads)
        <section class="mt-8" aria-labelledby="recent-leads">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="recent-leads" class="uh-h3">Recent leads</h2>
                <a class="uh-btn-outline uh-btn-sm" href="{{ route('admin.leads.index') }}">All leads</a>
            </div>

            @if($recentLeads->isNotEmpty())
                <div class="uh-panel-flush mt-4 overflow-hidden">
                    <div class="uh-table-scroll">
                        <table class="uh-table">
                            <caption class="sr-only">The eight most recent leads you can see</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Interested in</th>
                                    <th scope="col">Stage</th>
                                    <th scope="col">Assigned</th>
                                    <th scope="col">Received</th>
                                    <th scope="col"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentLeads as $lead)
                                    <tr>
                                        <td class="font-medium">
                                            <a class="uh-link-quiet" href="{{ route('admin.leads.show', $lead) }}">{{ $lead->name }}</a>
                                            <span class="mt-0.5 block text-xs font-normal text-[var(--color-muted)]" dir="ltr">{{ $lead->phone }}</span>
                                        </td>
                                        <td class="min-w-44 max-w-56 truncate">{{ $lead->property?->title ?? $lead->project?->name ?? $lead->typeLabel() }}</td>
                                        <td><x-ui.status :status="$lead->status" /></td>
                                        <td class="whitespace-nowrap">{{ $lead->assignee?->name ?? 'Unassigned' }}</td>
                                        <td class="whitespace-nowrap text-xs text-[var(--color-muted)]">{{ \App\Support\DisplayTimezone::format($lead->created_at) }}</td>
                                        <td class="uh-admin-row-actions">
                                            <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.leads.show', $lead) }}">Open</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <x-ui.empty class="mt-4" icon="inbox" title="No leads yet" description="Enquiries submitted on the public site land here straight away." />
            @endif
        </section>
    @endif
@endsection
