@extends('layouts.admin')
@section('title', 'Leads')

@php
    $hasFilters = collect($filters)->filter(fn ($value) => filled($value))->isNotEmpty();
    $canExport = auth()->user()->can('export', \App\Models\Lead::class);
    $seesAll = auth()->user()->canSeeAllLeads();
@endphp

@section('content')
    <x-ui.page-header compact title="Leads"
                      :description="$seesAll ? 'Every enquiry captured from the public site. Open leads with the most urgent follow-up come first.' : 'Leads assigned to you. Open leads with the most urgent follow-up come first.'" />

    <nav class="mt-5 flex flex-wrap gap-2" aria-label="Lead views">
        @foreach(['' => 'All', 'open' => 'Open', 'unread' => 'Unread', 'overdue' => 'Overdue'] as $value => $label)
            <a href="{{ route('admin.leads.index', array_filter(['view' => $value ?: null])) }}"
               @class(['uh-btn-sm', 'uh-btn-primary' => ($filters['view'] ?? '') === $value, 'uh-btn-outline' => ($filters['view'] ?? '') !== $value])
               @if(($filters['view'] ?? '') === $value) aria-current="page" @endif>
                {{ $label }}
                @if($value === 'overdue' && $overdueCount > 0)
                    <span class="uh-badge uh-badge-danger ml-1">{{ $overdueCount }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    <div @class(['mt-5 grid gap-4', 'lg:grid-cols-3' => $canExport])>
        <form method="GET" @class(['uh-panel', 'lg:col-span-2' => $canExport])>
            <h2 class="uh-h4">Filter</h2>
            @if(filled($filters['view'] ?? null))
                <input type="hidden" name="view" value="{{ $filters['view'] }}">
            @endif
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <x-ui.input name="q" label="Name, phone or email" :value="$filters['q'] ?? ''" type="search" />
                <x-ui.select name="status" label="Stage">
                    <option value="">Any stage</option>
                    @foreach(\App\Models\Lead::STATUS_LABELS as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="priority" label="Priority">
                    <option value="">Any priority</option>
                    @foreach(\App\Models\Lead::PRIORITIES as $priority)
                        <option value="{{ $priority }}" @selected(($filters['priority'] ?? '') === $priority)>{{ ucfirst($priority) }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="type" label="Type">
                    <option value="">Any type</option>
                    @foreach(\App\Models\Lead::TYPE_LABELS as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="source" label="Source" :value="$filters['source'] ?? ''" placeholder="google" />
                <x-ui.input name="utm_campaign" label="Campaign" :value="$filters['utm_campaign'] ?? ''" />
                @if($seesAll)
                    <x-ui.select name="assigned_to" label="Assigned to">
                        <option value="">Anyone</option>
                        <option value="unassigned" @selected(($filters['assigned_to'] ?? '') === 'unassigned')>Unassigned</option>
                        @foreach($salesUsers as $salesUser)
                            <option value="{{ $salesUser->id }}" @selected(($filters['assigned_to'] ?? '') == $salesUser->id)>{{ $salesUser->name }}</option>
                        @endforeach
                    </x-ui.select>
                @endif
                <x-ui.input name="from" label="Received from" type="date" :value="$filters['from'] ?? ''" />
                <x-ui.input name="to" label="Received to" type="date" :value="$filters['to'] ?? ''" />
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <button type="submit" class="uh-btn-primary uh-btn-sm">Apply filter</button>
                @if($hasFilters)
                    <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.leads.index') }}">Clear</a>
                @endif
            </div>
        </form>

        @if($canExport)
            <form method="GET" action="{{ route('admin.leads.export') }}" class="uh-panel">
                <h2 class="uh-h4">Export</h2>
                <p class="mt-1 text-xs text-[var(--color-muted)]">CSV of leads received between two dates. Every export is recorded in the audit log.</p>
                <div class="mt-4 space-y-3">
                    <x-ui.input name="from" label="From" type="date" id="export-from" />
                    <x-ui.input name="to" label="To" type="date" id="export-to" />
                    <x-ui.select name="status" label="Stage" id="export-status">
                        <option value="">Any stage</option>
                        @foreach(\App\Models\Lead::STATUS_LABELS as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
                <button type="submit" class="uh-btn-outline uh-btn-sm uh-btn-block mt-4">
                    <x-icon name="download" class="size-4" />
                    Export CSV
                </button>
            </form>
        @endif
    </div>

    @if($leads->isNotEmpty())
        <p class="mt-7 text-sm text-[var(--color-muted)]">
            <span class="uh-numeric font-semibold text-ink">{{ $leads->total() }}</span>
            {{ $leads->total() === 1 ? 'lead' : 'leads' }}{{ $hasFilters ? ' matching your filter' : '' }}
        </p>

        <div class="uh-panel-flush mt-3 overflow-hidden">
            <div class="uh-table-scroll">
                <table class="uh-table">
                    <caption class="sr-only">Captured leads</caption>
                    <thead>
                        <tr>
                            <th scope="col">Lead</th>
                            <th scope="col">Interested in</th>
                            <th scope="col">Stage</th>
                            <th scope="col">Priority</th>
                            <th scope="col">Next action</th>
                            <th scope="col">Source</th>
                            @if($seesAll)<th scope="col">Assigned</th>@endif
                            <th scope="col">Received</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leads as $lead)
                            <tr>
                                <td>
                                    <a @class(['uh-link-quiet', 'font-semibold' => $lead->is_unread, 'font-medium' => ! $lead->is_unread]) href="{{ route('admin.leads.show', $lead) }}">{{ $lead->name }}</a>
                                    @if($lead->is_unread)<span class="sr-only">(unread)</span><span class="ml-1 inline-block size-2 rounded-full bg-forest" aria-hidden="true"></span>@endif
                                    <span class="mt-0.5 block text-xs text-[var(--color-muted)]" dir="ltr">{{ $lead->phone }}</span>
                                    @if($lead->is_repeat_contact)<x-ui.badge tone="warn" class="mt-1">Repeat</x-ui.badge>@endif
                                </td>
                                <td class="min-w-40 max-w-52 truncate">
                                    {{ $lead->property?->title ?? $lead->project?->name ?? $lead->typeLabel() }}
                                </td>
                                <td><x-ui.status :status="$lead->status" /></td>
                                <td><x-ui.status :status="$lead->priority" /></td>
                                <td @class(['whitespace-nowrap text-xs', 'font-semibold text-[var(--color-danger)]' => $lead->isOverdue()])>
                                    {{ $lead->next_action_at ? \App\Support\DisplayTimezone::format($lead->next_action_at) : '—' }}
                                </td>
                                <td class="whitespace-nowrap text-xs">
                                    {{ $lead->hasAttribution() ? ($lead->utm_source ?? $lead->source) : 'Direct or unknown' }}
                                </td>
                                @if($seesAll)<td class="whitespace-nowrap">{{ $lead->assignee?->name ?? 'Unassigned' }}</td>@endif
                                <td class="whitespace-nowrap text-xs text-[var(--color-muted)]">
                                    {{ \App\Support\DisplayTimezone::format($lead->created_at) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($leads->hasPages())
            <div class="mt-6">{{ $leads->links() }}</div>
        @endif
    @else
        <x-ui.empty class="mt-7" icon="inbox"
                    :title="$hasFilters ? 'No leads match this filter' : 'No leads yet'"
                    :description="$hasFilters ? 'Try a wider date range or clear some filters.' : 'Enquiries and visit requests submitted on the public site appear here immediately.'">
            @if($hasFilters)
                <a class="uh-btn-primary uh-btn-sm" href="{{ route('admin.leads.index') }}">Clear filter</a>
            @endif
        </x-ui.empty>
    @endif
@endsection
