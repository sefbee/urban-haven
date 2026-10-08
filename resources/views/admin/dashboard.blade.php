@extends('layouts.admin')
@section('title', 'Dashboard')

@php
    $user = auth()->user();
    $hour = (int) now(config('urbanhaven.display_timezone'))->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $firstName = \Illuminate\Support\Str::of((string) $user->name)->explode(' ')->first();

    $delta = function (int $current, ?int $previous): ?array {
        if ($previous === null) {
            return null;
        }
        if ($previous === 0) {
            return $current > 0 ? ['label' => 'New', 'up' => true] : null;
        }
        $change = (int) round((($current - $previous) / $previous) * 100);

        return ['label' => ($change >= 0 ? '+' : '').$change.'%', 'up' => $change >= 0];
    };

    $stats = [];
    if ($seesLeads) {
        $stats[] = ['label' => 'New leads', 'value' => $leadStats['new'], 'icon' => 'inbox', 'url' => route('admin.leads.index', ['status' => 'new']), 'sub' => $leadStats['unread'].' unread', 'accent' => true];
        $stats[] = ['label' => 'Leads in 30 days', 'value' => $leadStats['received30'], 'icon' => 'calendar', 'url' => route('admin.leads.index'), 'sub' => 'vs previous 30 days', 'delta' => $delta($leadStats['received30'], $previousStats['received30'])];
        $stats[] = ['label' => 'Won in 30 days', 'value' => $leadStats['won30'], 'icon' => 'check-circle', 'url' => route('admin.leads.index', ['status' => 'won']), 'sub' => 'vs previous 30 days', 'delta' => $delta($leadStats['won30'], $previousStats['won30'])];
        $stats[] = ['label' => 'Upcoming visits', 'value' => $leadStats['visits'], 'icon' => 'map', 'url' => route('admin.visits.index', ['view' => 'upcoming']), 'sub' => 'requested or confirmed'];
    } elseif ($seesInventory) {
        $stats[] = ['label' => 'Live listings', 'value' => $liveListings, 'icon' => 'home', 'url' => route('admin.properties.index'), 'sub' => 'published on the website', 'accent' => true];
        $stats[] = ['label' => 'Draft listings', 'value' => $drafts, 'icon' => 'document', 'url' => route('admin.properties.index', ['status' => 'draft']), 'sub' => 'not yet published'];
        if ($canReview) {
            $stats[] = ['label' => 'Awaiting review', 'value' => $pendingReview, 'icon' => 'check-circle', 'url' => route('admin.review.index'), 'sub' => 'properties and projects'];
        }
        $stats[] = ['label' => 'Expiring reservations', 'value' => $expiringReservations->count(), 'icon' => 'clock', 'url' => route('admin.properties.index', ['availability' => 'reserved']), 'sub' => 'within 3 days'];
    }

    $decisions = array_values(array_filter([
        $seesLeads && $leadStats['overdue'] > 0 ? ['tone' => 'danger', 'icon' => 'clock', 'count' => $leadStats['overdue'], 'label' => \Illuminate\Support\Str::plural('follow-up', $leadStats['overdue']).' overdue', 'url' => route('admin.follow-ups.index')] : null,
        $seesLeads && $leadStats['unread'] > 0 ? ['tone' => 'info', 'icon' => 'inbox', 'count' => $leadStats['unread'], 'label' => 'unread '.\Illuminate\Support\Str::plural('lead', $leadStats['unread']), 'url' => route('admin.leads.index', ['view' => 'unread'])] : null,
        $canReview && $pendingReview > 0 ? ['tone' => 'warn', 'icon' => 'check-circle', 'count' => $pendingReview, 'label' => \Illuminate\Support\Str::plural('listing', $pendingReview).' waiting for review', 'url' => route('admin.review.index')] : null,
        $seesInventory && $expiringReservations->isNotEmpty() ? ['tone' => 'warn', 'icon' => 'key', 'count' => $expiringReservations->count(), 'label' => \Illuminate\Support\Str::plural('reservation', $expiringReservations->count()).' ending within 3 days', 'url' => route('admin.properties.index', ['availability' => 'reserved'])] : null,
        $cmsDrafts > 0 ? ['tone' => 'warn', 'icon' => 'document', 'count' => $cmsDrafts, 'label' => 'website '.\Illuminate\Support\Str::plural('edit', $cmsDrafts).' waiting to be published', 'url' => route('admin.home-sections.index')] : null,
        $seesLeads && $leadStats['visits'] > 0 ? ['tone' => 'neutral', 'icon' => 'calendar', 'count' => $leadStats['visits'], 'label' => 'upcoming site '.\Illuminate\Support\Str::plural('visit', $leadStats['visits']), 'url' => route('admin.visits.index', ['view' => 'upcoming'])] : null,
    ]));

    $quickActions = array_values(array_filter([
        $user->can('create', \App\Models\Property::class) ? ['label' => 'Add property', 'icon' => 'home', 'url' => route('admin.properties.create')] : null,
        $user->can('create', \App\Models\Project::class) ? ['label' => 'Add project', 'icon' => 'building', 'url' => route('admin.projects.create')] : null,
        $user->can('create', \App\Models\Post::class) ? ['label' => 'Write article', 'icon' => 'document', 'url' => route('admin.posts.create')] : null,
        $homeSectionsShown !== null ? ['label' => 'Edit home page', 'icon' => 'grid', 'url' => route('admin.home-sections.index')] : null,
        $user->isOwnerAdmin() ? ['label' => 'Website settings', 'icon' => 'settings', 'url' => route('admin.settings.index')] : null,
        $seesLeads ? ['label' => 'Export leads', 'icon' => 'download', 'url' => route('admin.leads.index')] : null,
    ]));

    $chartMax = max([1, ...$activity['received'], ...$activity['won']]);
    $chartStep = max(1, (int) ceil($chartMax / 4));
    $chartTop = $chartStep * 4;
@endphp

@section('content')
<div class="dd-dashboard">
    <section class="dd-hero" aria-labelledby="dd-hero-title">
        <div class="dd-hero-copy">
            <p class="dd-hero-date">{{ now(config('urbanhaven.display_timezone'))->format('l, j F Y') }}</p>
            <h1 id="dd-hero-title" class="dd-hero-title">{{ $greeting }}, {{ $firstName }}</h1>
            <p class="dd-hero-summary">
                @if($decisions === [])
                    Everything is on track. Nothing is overdue or waiting for approval.
                @else
                    {{ count($decisions) }} {{ \Illuminate\Support\Str::plural('thing', count($decisions)) }} {{ count($decisions) === 1 ? 'needs' : 'need' }} your attention today.
                @endif
            </p>
        </div>
        <div class="dd-hero-actions">
            @if($seesLeads)
                <a class="dd-hero-btn" href="{{ route('admin.leads.index', ['view' => 'open']) }}">
                    <x-icon name="inbox" class="size-4" /> Open leads
                </a>
            @endif
            <a class="dd-hero-btn" href="{{ url('/') }}" target="_blank" rel="noopener">
                <x-icon name="external" class="size-4" /> Live site
            </a>
            @if($user->can('create', \App\Models\Property::class))
                <a class="dd-hero-btn is-solid" href="{{ route('admin.properties.create') }}">
                    <x-icon name="plus" class="size-4" /> New property
                </a>
            @endif
        </div>
    </section>

    @if(count($stats))
        <div class="dd-stat-row">
            @foreach($stats as $stat)
                <a href="{{ $stat['url'] }}" class="dd-stat-card">
                    <div class="dd-stat-top">
                        <span class="dd-stat-icon"><x-icon :name="$stat['icon']" class="size-5" /></span>
                        @if(! empty($stat['delta']))
                            <span @class(['dd-delta', 'dd-delta-up' => $stat['delta']['up'], 'dd-delta-down' => ! $stat['delta']['up']])>{{ $stat['delta']['label'] }}</span>
                        @endif
                    </div>
                    <div class="dd-stat-value">{{ number_format($stat['value']) }}</div>
                    <div class="dd-stat-label">{{ $stat['label'] }}</div>
                    <div class="dd-stat-sub">{{ $stat['sub'] }}</div>
                </a>
            @endforeach
        </div>
    @endif

    <div class="dd-split">
        <div class="dd-stack">
            <section class="dd-card" aria-labelledby="decisions-heading">
                <div class="dd-card-head">
                    <div>
                        <h2 id="decisions-heading" class="dd-card-title">Needs your attention</h2>
                        <p class="dd-card-subtitle">Most urgent first.</p>
                    </div>
                </div>
                @if($decisions === [])
                    <div class="dd-all-clear">
                        <span class="dd-all-clear-icon"><x-icon name="check" class="size-5" /></span>
                        <div>
                            <p class="font-semibold">All clear</p>
                            <p class="text-sm text-[var(--dd-muted)]">Nothing is overdue or waiting for approval.</p>
                        </div>
                    </div>
                @else
                    <ul class="dd-decision-list">
                        @foreach($decisions as $decision)
                            <li>
                                <a href="{{ $decision['url'] }}" class="dd-decision dd-decision-{{ $decision['tone'] }}">
                                    <span class="dd-decision-icon"><x-icon :name="$decision['icon']" class="size-4" /></span>
                                    <span class="dd-decision-count">{{ number_format($decision['count']) }}</span>
                                    <span class="dd-decision-label">{{ $decision['label'] }}</span>
                                    <x-icon name="arrow-right" class="size-4 text-[var(--dd-faint)]" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
            @if($seesLeads)
                <section class="dd-card" aria-labelledby="activity-heading">
                    <div class="dd-card-head">
                        <div>
                            <h2 id="activity-heading" class="dd-card-title">Lead activity</h2>
                            <p class="dd-card-subtitle">Received and won, last 6 months</p>
                        </div>
                        <div class="dd-chart-legend">
                            <span class="dd-legend-item dd-legend-gray">Received</span>
                            <span class="dd-legend-item dd-legend-blue">Won</span>
                        </div>
                    </div>
                    <div class="dd-chart-wrap">
                        <svg viewBox="0 0 520 210" class="dd-bar-chart" role="img" aria-label="Leads received and won per month">
                            @foreach(range(0, 4) as $step)
                                @php $yPos = 185 - ($step / 4) * 160; @endphp
                                <text x="36" y="{{ $yPos + 3 }}" class="dd-chart-label" text-anchor="end">{{ $step * $chartStep }}</text>
                                <line x1="42" y1="{{ $yPos }}" x2="510" y2="{{ $yPos }}" class="dd-chart-grid"/>
                            @endforeach
                            @foreach($activity['months'] as $index => $month)
                                @php
                                    $x = 62 + $index * 76;
                                    $receivedHeight = max(2, ($activity['received'][$index] / $chartTop) * 160);
                                    $wonHeight = max(2, ($activity['won'][$index] / $chartTop) * 160);
                                @endphp
                                <rect x="{{ $x }}" y="{{ 185 - $receivedHeight }}" width="18" height="{{ $receivedHeight }}" rx="4" class="dd-bar-seen">
                                    <title>{{ $month }}: {{ $activity['received'][$index] }} received</title>
                                </rect>
                                <rect x="{{ $x + 22 }}" y="{{ 185 - $wonHeight }}" width="18" height="{{ $wonHeight }}" rx="4" class="dd-bar-sales">
                                    <title>{{ $month }}: {{ $activity['won'][$index] }} won</title>
                                </rect>
                                <text x="{{ $x + 20 }}" y="202" class="dd-chart-label" text-anchor="middle">{{ $month }}</text>
                            @endforeach
                        </svg>
                    </div>
                </section>
            @endif
            @if($seesLeads && $stageCounts->isNotEmpty())
                @php $pipelineMax = max(1, (int) $stageCounts->max()); @endphp
                <section class="dd-card" aria-labelledby="pipeline-heading">
                    <div class="dd-card-head">
                        <div>
                            <h2 id="pipeline-heading" class="dd-card-title">Lead pipeline</h2>
                            <p class="dd-card-subtitle">Every lead you can see, by stage</p>
                        </div>
                    </div>
                    <dl class="dd-pipeline-list">
                        @foreach(\App\Models\Lead::STATUS_LABELS as $status => $label)
                            @php $count = (int) ($stageCounts[$status] ?? 0); @endphp
                            <div class="dd-pipeline-row">
                                <dt><a class="dd-pipeline-link" href="{{ route('admin.leads.index', ['status' => $status]) }}">{{ $label }}</a></dt>
                                <dd class="dd-pipeline-count">{{ number_format($count) }}</dd>
                                <div class="dd-pipeline-bar-wrap"><div class="dd-pipeline-bar" style="width: {{ round(($count / $pipelineMax) * 100) }}%"></div></div>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endif
        </div>
        <div class="dd-stack">
            @if($quickActions !== [])
                <section class="dd-card" aria-labelledby="quick-heading">
                    <div class="dd-card-head">
                        <h2 id="quick-heading" class="dd-card-title">Quick actions</h2>
                    </div>
                    <ul class="dd-action-list">
                        @foreach($quickActions as $action)
                            <li>
                                <a href="{{ $action['url'] }}" class="dd-action-row">
                                    <span class="dd-quick-icon"><x-icon :name="$action['icon']" class="size-4" /></span>
                                    <span class="min-w-0 flex-1 truncate">{{ $action['label'] }}</span>
                                    <x-icon name="chevron-right" class="size-4 text-[var(--dd-faint)]" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
            @if($seesInventory)
                @php
                    $inventoryTotal = max(1, (int) $inventory->sum());
                    $available = (int) ($inventory['available'] ?? 0);
                    $circumference = 2 * M_PI * 42;
                @endphp
                <section class="dd-card" aria-labelledby="inventory-heading">
                    <div class="dd-card-head">
                        <div>
                            <h2 id="inventory-heading" class="dd-card-title">Live inventory</h2>
                            <p class="dd-card-subtitle">Published listings by availability</p>
                        </div>
                    </div>
                    <div class="dd-donut-row">
                        <div class="dd-donut-wrap">
                            <svg viewBox="0 0 100 100" class="dd-donut-svg" aria-hidden="true">
                                <circle cx="50" cy="50" r="42" fill="none" stroke="#eef2ff" stroke-width="12"/>
                                <circle cx="50" cy="50" r="42" fill="none" stroke="url(#dd-donut-gradient)" stroke-width="12" stroke-linecap="round"
                                        stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $circumference * (1 - ($inventory->sum() > 0 ? $available / $inventoryTotal : 0)) }}"/>
                                <defs><linearGradient id="dd-donut-gradient"><stop offset="0" stop-color="#3b5bdb"/><stop offset="1" stop-color="#5f3dc4"/></linearGradient></defs>
                            </svg>
                            <div class="dd-donut-center">
                                <span class="dd-donut-value">{{ number_format($liveListings) }}</span>
                                <span class="dd-donut-label">live</span>
                            </div>
                        </div>
                        <ul class="dd-category-list">
                            @foreach(\App\Models\Property::AVAILABILITY_LABELS as $availability => $label)
                                <li class="dd-category-row">
                                    <a class="dd-category-name uh-link-quiet" href="{{ route('admin.properties.index', ['availability' => $availability]) }}">{{ $label }}</a>
                                    <span class="dd-category-count">{{ number_format((int) ($inventory[$availability] ?? 0)) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    @if($expiringReservations->isNotEmpty())
                        <p class="dd-card-section-label">Reservations ending soon</p>
                        <ul class="dd-reservation-list">
                            @foreach($expiringReservations as $reserved)
                                <li class="dd-reservation-row">
                                    <a class="dd-reservation-name" href="{{ route('admin.properties.edit', $reserved) }}">{{ $reserved->reference ?? $reserved->title }}</a>
                                    <span class="dd-reservation-date">{{ \App\Support\DisplayTimezone::format($reserved->reservation_expires_at) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endif
            @if($seesLeads)
                <section class="dd-card" aria-labelledby="month-heading">
                    <div class="dd-card-head">
                        <div>
                            <h2 id="month-heading" class="dd-card-title">Last 30 days</h2>
                            <p class="dd-card-subtitle">Outcomes and where enquiries came from</p>
                        </div>
                    </div>
                    <div class="dd-mini-stats">
                        <div class="dd-mini-stat"><span class="dd-mini-stat-value">{{ number_format($leadStats['received30']) }}</span><span class="dd-mini-stat-label">Received</span></div>
                        <div class="dd-mini-stat"><span class="dd-mini-stat-value text-[var(--dd-green)]">{{ number_format($leadStats['won30']) }}</span><span class="dd-mini-stat-label">Won</span></div>
                        <div class="dd-mini-stat"><span class="dd-mini-stat-value text-[var(--dd-red)]">{{ number_format($leadStats['lost30']) }}</span><span class="dd-mini-stat-label">Lost</span></div>
                    </div>
                    @if($sourceCounts->isNotEmpty())
                        <p class="dd-card-section-label">Where leads came from</p>
                        <dl class="dd-pipeline-list">
                            @foreach($sourceCounts as $channel => $total)
                                <div class="dd-pipeline-row">
                                    <dt><span class="dd-pipeline-link">{{ $channel === 'direct' ? 'Direct / unknown' : \Illuminate\Support\Str::headline($channel) }}</span></dt>
                                    <dd class="dd-pipeline-count">{{ number_format($total) }}</dd>
                                    <div class="dd-pipeline-bar-wrap"><div class="dd-pipeline-bar" style="width: {{ round(($total / max(1, $sourceCounts->max())) * 100) }}%"></div></div>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                </section>
            @endif
        </div>
    </div>

    @if(! $seesLeads && ! $seesInventory && $quickActions === [])
        <div class="dd-card">
            <x-ui.empty icon="dashboard" title="Nothing to show yet" description="Your role does not include leads or inventory." />
        </div>
    @endif

    @if($seesLeads && $recentLeads->isNotEmpty())
        <section class="dd-card dd-table-card" aria-labelledby="recent-heading">
            <div class="dd-card-head">
                <div>
                    <h2 id="recent-heading" class="dd-card-title">Recent leads</h2>
                    <p class="dd-card-subtitle">The newest enquiries across the website</p>
                </div>
                <a class="dd-btn-outline" href="{{ route('admin.leads.index') }}">All leads</a>
            </div>
            <div class="uh-table-scroll">
                <table class="uh-table">
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
                                <td class="uh-admin-row-actions"><a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.leads.show', $lead) }}">Open</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @elseif($seesLeads)
        <div class="dd-card">
            <x-ui.empty icon="inbox" title="No leads yet" description="Enquiries submitted on the public site land here." />
        </div>
    @endif
</div>
@endsection
