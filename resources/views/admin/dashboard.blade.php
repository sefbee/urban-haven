@extends('layouts.admin')
@section('title', 'Dashboard')

@php
    use Carbon\Carbon;

    $today = Carbon::now()->format('l, F jS Y');

    // Build stat cards from real data only
    $stats = [];
    if ($seesLeads) {
        $stats[] = [
            'label'  => 'New Leads',
            'value'  => number_format($leadStats['new']),
            'icon'   => 'inbox',
            'url'    => route('admin.leads.index', ['status' => 'new']),
            'sub'    => 'vs last 30 days',
            'accent' => true,
        ];
        $stats[] = [
            'label' => 'Leads (30 days)',
            'value' => number_format($leadStats['received30']),
            'icon'  => 'calendar',
            'url'   => route('admin.leads.index'),
            'sub'   => 'received this month',
        ];
        $stats[] = [
            'label' => 'Overdue Follow-ups',
            'value' => number_format($leadStats['overdue']),
            'icon'  => 'clock',
            'url'   => route('admin.follow-ups.index'),
            'sub'   => 'need action now',
            'alert' => $leadStats['overdue'] > 0,
        ];
        $stats[] = [
            'label' => 'Upcoming Visits',
            'value' => number_format($leadStats['visits']),
            'icon'  => 'check-circle',
            'url'   => route('admin.visits.index', ['view' => 'upcoming']),
            'sub'   => 'scheduled site visits',
        ];
    } elseif ($seesInventory) {
        $totalInventory = $inventory->sum();
        $stats[] = [
            'label'  => 'Published Listings',
            'value'  => number_format($totalInventory),
            'icon'   => 'home',
            'url'    => route('admin.properties.index'),
            'sub'    => 'live properties',
            'accent' => true,
        ];
        $stats[] = [
            'label' => 'Draft Listings',
            'value' => number_format($drafts),
            'icon'  => 'document',
            'url'   => route('admin.properties.index', ['status' => 'draft']),
            'sub'   => 'awaiting publish',
        ];
        if ($canReview) {
            $stats[] = [
                'label' => 'Awaiting Review',
                'value' => number_format($pendingReview),
                'icon'  => 'check-circle',
                'url'   => route('admin.review.index'),
                'sub'   => 'pending approval',
                'alert' => $pendingReview > 0,
            ];
        }
        $stats[] = [
            'label' => 'Expiring Reservations',
            'value' => number_format($expiringReservations->count()),
            'icon'  => 'clock',
            'url'   => route('admin.properties.index', ['availability' => 'reserved']),
            'sub'   => 'within 3 days',
            'alert' => $expiringReservations->isNotEmpty(),
        ];
    }

    // Pipeline chart data from real stageCounts
    $pipelineMax = max([(int) $stageCounts->max(), 1]);

    // Bar chart: last 6 months of received leads per month
    $chartMonths = [];
    $chartReceived = [];
    $chartWon = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = now()->subMonths($i);
        $chartMonths[] = $month->format('M');
        $chartReceived[] = $seesLeads
            ? \App\Models\Lead::query()->visibleTo(auth()->user())
                ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count()
            : 0;
        $chartWon[] = $seesLeads
            ? \App\Models\Lead::query()->visibleTo(auth()->user())
                ->where('status', 'won')
                ->whereBetween('updated_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count()
            : 0;
    }
    $chartMax = max([max($chartReceived), max($chartWon), 1]);

    // Inventory breakdown for right panel
    $availabilityLabels = \App\Models\Property::AVAILABILITY_LABELS ?? [];
@endphp

@section('content')
<div class="dd-inner-panel">
<div class="dd-dashboard">

    {{-- Page Header --}}
    <div class="dd-page-header">
        <div>
            <h1 class="dd-page-title">Dashboard</h1>
            <p class="dd-page-date">{{ $today }}</p>
        </div>
        <div class="dd-header-actions">
            @if($seesLeads)
                <a class="dd-btn-outline" href="{{ route('admin.leads.index', ['view' => 'open']) }}">Open leads</a>
            @endif
            @if($seesInventory && auth()->user()->hasPermission('property.create'))
                <a class="dd-btn-primary" href="{{ route('admin.properties.create') }}">
                    <x-icon name="plus" class="size-4" /> New Property
                </a>
            @endif
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="dd-main-grid">

        {{-- Left Column --}}
        <div class="dd-left-col">

            {{-- Stat Cards 2×2 --}}
            @if(count($stats))
            <div class="dd-stat-grid">
                @foreach($stats as $i => $stat)
                <a href="{{ $stat['url'] }}" @class(['dd-stat-card', 'dd-stat-accent' => !empty($stat['accent']), 'dd-stat-alert' => !empty($stat['alert'])])>
                    <div class="dd-stat-top">
                        <span @class(['dd-stat-icon', 'dd-stat-icon-white' => !empty($stat['accent']), 'dd-stat-icon-alert' => !empty($stat['alert']) && empty($stat['accent'])])>
                            <x-icon :name="$stat['icon']" class="size-5" />
                        </span>
                    </div>
                    <div class="dd-stat-label">{{ $stat['label'] }}</div>
                    <div class="dd-stat-value">{{ $stat['value'] }}</div>
                    <div class="dd-stat-sub">{{ $stat['sub'] }}</div>
                </a>
                @endforeach
            </div>
            @endif

            {{-- Lead Pipeline / Inventory Summary --}}
            @if($seesLeads && $stageCounts->isNotEmpty())
            <div class="dd-card">
                <div class="dd-chart-header">
                    <div>
                        <div class="dd-card-title">Lead Pipeline</div>
                        <div class="dd-card-subtitle">Current leads by stage</div>
                    </div>
                </div>
                <dl class="dd-pipeline-list">
                    @foreach(\App\Models\Lead::STATUS_LABELS as $status => $label)
                        @php $count = $stageCounts[$status] ?? 0; @endphp
                        <div class="dd-pipeline-row">
                            <dt>
                                <a class="dd-pipeline-link" href="{{ route('admin.leads.index', ['status' => $status]) }}">{{ $label }}</a>
                            </dt>
                            <dd class="dd-pipeline-count">{{ number_format($count) }}</dd>
                            <div class="dd-pipeline-bar-wrap">
                                <div class="dd-pipeline-bar" style="width: {{ $pipelineMax > 0 ? round(($count / $pipelineMax) * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </dl>
            </div>
            @elseif($seesInventory && $inventory->isNotEmpty())
            <div class="dd-card">
                <div class="dd-chart-header">
                    <div>
                        <div class="dd-card-title">Published Inventory</div>
                        <div class="dd-card-subtitle">Live listings by availability</div>
                    </div>
                </div>
                <dl class="dd-pipeline-list">
                    @foreach($availabilityLabels as $availability => $label)
                        @php $count = $inventory[$availability] ?? 0; @endphp
                        <div class="dd-pipeline-row">
                            <dt>
                                <a class="dd-pipeline-link" href="{{ route('admin.properties.index', ['availability' => $availability]) }}">{{ $label }}</a>
                            </dt>
                            <dd class="dd-pipeline-count">{{ number_format($count) }}</dd>
                            <div class="dd-pipeline-bar-wrap">
                                <div class="dd-pipeline-bar" style="width: {{ $inventory->sum() > 0 ? round(($count / $inventory->sum()) * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </dl>
            </div>
            @endif

            {{-- Bar Chart: Activity over last 6 months --}}
            @if($seesLeads)
            <div class="dd-card dd-chart-card">
                <div class="dd-chart-header">
                    <div>
                        <div class="dd-card-title">Lead Activity</div>
                        <div class="dd-card-subtitle">Received vs won — last 6 months</div>
                    </div>
                </div>
                <div class="dd-chart-legend">
                    <span class="dd-legend-item dd-legend-gray">Received</span>
                    <span class="dd-legend-item dd-legend-blue">Won</span>
                </div>
                <div class="dd-chart-wrap">
                    <svg viewBox="0 0 520 210" class="dd-bar-chart" preserveAspectRatio="xMidYMid meet">
                        @php
                            $chartH = 160; $chartBase = 185;
                            $barW = 16; $gap = 12; $startX = 45;
                            $yLabels = [0, 25, 50, 75, 100];
                        @endphp
                        @foreach($yLabels as $yLabel)
                            @php $yPos = $chartBase - ($yLabel / 100) * $chartH; @endphp
                            <text x="36" y="{{ $yPos + 4 }}" class="dd-chart-label" text-anchor="end">{{ $yLabel }}%</text>
                            <line x1="42" y1="{{ $yPos }}" x2="510" y2="{{ $yPos }}" class="dd-chart-grid"/>
                        @endforeach

                        @foreach($chartMonths as $mi => $month)
                            @php
                                $x = $startX + $mi * (($barW * 2) + $gap + 10);
                                $rH = $chartMax > 0 ? ($chartReceived[$mi] / $chartMax) * $chartH : 1;
                                $wH = $chartMax > 0 ? ($chartWon[$mi] / $chartMax) * $chartH : 1;
                                $rH = max($rH, 3);
                                $wH = max($wH, 3);
                            @endphp
                            <rect x="{{ $x }}" y="{{ $chartBase - $rH }}" width="{{ $barW }}" height="{{ $rH }}" rx="4" class="dd-bar-seen"/>
                            <rect x="{{ $x + $barW + 4 }}" y="{{ $chartBase - $wH }}" width="{{ $barW }}" height="{{ $wH }}" rx="4" class="dd-bar-sales"/>
                            <text x="{{ $x + $barW }}" y="{{ $chartBase + 16 }}" class="dd-chart-label" text-anchor="middle">{{ $month }}</text>
                        @endforeach
                    </svg>
                </div>
            </div>
            @endif

        </div>{{-- end left --}}

        {{-- Right Column --}}
        <div class="dd-right-col">

            {{-- Lead Stats Summary --}}
            @if($seesLeads)
            <div class="dd-card">
                <div class="dd-chart-header">
                    <div>
                        <div class="dd-card-title">Last 30 Days</div>
                        <div class="dd-card-subtitle">Lead performance summary</div>
                    </div>
                </div>
                <div class="dd-stats-row">
                    <div class="dd-mini-stat">
                        <div class="dd-mini-stat-value">{{ number_format($leadStats['received30']) }}</div>
                        <div class="dd-mini-stat-label">Received</div>
                    </div>
                    <div class="dd-mini-stat">
                        <div class="dd-mini-stat-value dd-mini-stat-green">{{ number_format($leadStats['won30']) }}</div>
                        <div class="dd-mini-stat-label">Won</div>
                    </div>
                    <div class="dd-mini-stat">
                        <div class="dd-mini-stat-value dd-mini-stat-red">{{ number_format($leadStats['lost30']) }}</div>
                        <div class="dd-mini-stat-label">Lost</div>
                    </div>
                </div>

                @if($sourceCounts->isNotEmpty())
                <div class="dd-card-section-label">By source</div>
                <dl class="dd-pipeline-list mt-2">
                    @foreach($sourceCounts as $channel => $total)
                    <div class="dd-pipeline-row">
                        <dt><span class="dd-pipeline-link">{{ $channel === 'direct' ? 'Direct / unknown' : $channel }}</span></dt>
                        <dd class="dd-pipeline-count">{{ number_format($total) }}</dd>
                        <div class="dd-pipeline-bar-wrap">
                            <div class="dd-pipeline-bar" style="width: {{ $sourceCounts->max() > 0 ? round(($total / $sourceCounts->max()) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </dl>
                @endif
            </div>
            @endif

            {{-- Expiring Reservations --}}
            @if($seesInventory && $expiringReservations->isNotEmpty())
            <div class="dd-card">
                <div class="dd-chart-header">
                    <div>
                        <div class="dd-card-title">Expiring Reservations</div>
                        <div class="dd-card-subtitle">Ending within 3 days</div>
                    </div>
                </div>
                <ul class="dd-reservation-list">
                    @foreach($expiringReservations as $reserved)
                    <li class="dd-reservation-row">
                        <a class="dd-reservation-name" href="{{ route('admin.properties.edit', $reserved) }}">
                            {{ $reserved->reference ?? $reserved->title }}
                        </a>
                        <span class="dd-reservation-date">{{ \App\Support\DisplayTimezone::format($reserved->reservation_expires_at) }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Empty state for right column --}}
            @if(!$seesLeads && !$seesInventory)
            <div class="dd-card">
                <x-ui.empty icon="dashboard" title="Nothing to show" description="You don't have access to leads or inventory." />
            </div>
            @endif

        </div>{{-- end right --}}
    </div>{{-- end main grid --}}

    {{-- Recent Leads Table --}}
    @if($seesLeads && $recentLeads->isNotEmpty())
    <div class="dd-card dd-table-card">
        <div class="dd-chart-header">
            <div class="dd-card-title">Recent Leads</div>
            <a class="dd-btn-outline" href="{{ route('admin.leads.index') }}">All leads</a>
        </div>
        <div class="uh-table-scroll mt-4">
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
                        <td class="uh-admin-row-actions">
                            <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.leads.show', $lead) }}">Open</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @elseif($seesLeads)
        <x-ui.empty class="mt-4" icon="inbox" title="No leads yet" description="Enquiries submitted on the public site land here." />
    @endif

</div>{{-- end dd-dashboard --}}
</div>{{-- end dd-inner-panel --}}
@endsection
