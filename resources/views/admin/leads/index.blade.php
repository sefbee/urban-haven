@extends('layouts.admin')
@section('title', 'Leads')

@php
    $hasFilters = collect($filters)->filter(fn ($value) => filled($value))->isNotEmpty();
    $advancedOpen = collect($filters)->only(['priority', 'type', 'source', 'utm_campaign', 'assigned_to', 'from', 'to'])->filter(fn ($value) => filled($value))->isNotEmpty();
    $canExport = auth()->user()->can('export', \App\Models\Lead::class);
    $seesAll = auth()->user()->canSeeAllLeads();
    $view = $filters['view'] ?? '';
@endphp

@section('content')
    <x-ui.page-header compact title="Leads"
                      :description="$seesAll ? 'Every enquiry captured from the public site. Open leads with the most urgent follow-up come first.' : 'Leads assigned to you. Open leads with the most urgent follow-up come first.'">
        <x-slot:eyebrow>Communication</x-slot:eyebrow>
        @if($canExport)
            <x-slot:actions>
                <a class="uh-btn-outline uh-btn-sm" href="#lead-export">
                    <x-icon name="download" class="size-4" />
                    Export CSV
                </a>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    <x-ui.admin-related label="Next to this">
        <a href="{{ route('admin.follow-ups.index') }}">Follow-ups</a>
        <a href="{{ route('admin.visits.index') }}">Site visits</a>
    </x-ui.admin-related>

    <x-ui.admin-tabs label="Lead views">
        @foreach(['' => 'All', 'open' => 'Open', 'unread' => 'Unread', 'overdue' => 'Overdue'] as $value => $label)
            <a href="{{ route('admin.leads.index', array_filter(['view' => $value ?: null])) }}"
               @class(['is-active' => $view === $value])
               @if($view === $value) aria-current="page" @endif>
                {{ $label }}
                @if($value === 'overdue' && $overdueCount > 0)
                    <span class="uh-badge uh-badge-danger">{{ $overdueCount }}</span>
                @endif
            </a>
        @endforeach
    </x-ui.admin-tabs>

    <form method="GET" class="uh-admin-toolbar">
        @if(filled($view))
            <input type="hidden" name="view" value="{{ $view }}">
        @endif
        <x-ui.input name="q" label="Name, phone or email" :value="$filters['q'] ?? ''" type="search" />
        <x-ui.select name="status" label="Stage">
            <option value="">Any stage</option>
            @foreach(\App\Models\Lead::STATUS_LABELS as $value => $label)
                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </x-ui.select>
        <div class="uh-admin-toolbar-actions">
            <button type="submit" class="uh-btn-primary uh-btn-sm">Apply filter</button>
            @if($hasFilters)
                <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.leads.index') }}">Clear</a>
            @endif
        </div>
        <details class="uh-admin-more basis-full" @if($advancedOpen) open @endif>
            <summary>More filters</summary>
            <div class="uh-admin-toolbar" style="padding: 0; margin: 0; border: 0; box-shadow: none;">
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
        </details>
    </form>

    @if($canExport)
        <details id="lead-export" class="uh-admin-toolbar mb-4">
            <summary class="cursor-pointer text-sm font-semibold">Export CSV</summary>
            <p class="mt-1 text-xs text-[var(--color-muted)]">Leads received between two dates. Every export is recorded in the audit log.</p>
            <form method="GET" action="{{ route('admin.leads.export') }}" class="mt-3 flex flex-wrap items-end gap-3">
                <x-ui.input name="from" label="Export from" type="date" id="export-from" />
                <x-ui.input name="to" label="Export to" type="date" id="export-to" />
                <x-ui.select name="status" label="Stage" id="export-status">
                    <option value="">Any stage</option>
                    @foreach(\App\Models\Lead::STATUS_LABELS as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <div class="uh-admin-toolbar-actions">
                    <button type="submit" class="uh-btn-outline uh-btn-sm">
                        <x-icon name="download" class="size-4" />
                        Download
                    </button>
                </div>
            </form>
        </details>
    @endif

    @if($leads->isNotEmpty())
        <p class="uh-admin-count">
            <span class="uh-numeric font-semibold text-ink">{{ $leads->total() }}</span>
            {{ $leads->total() === 1 ? 'lead' : 'leads' }}{{ $hasFilters ? ' matching your filter' : '' }}
        </p>

        <div class="uh-panel-flush overflow-hidden">
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
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leads as $lead)
                            <tr class="uh-admin-clickrow">
                                <td>
                                    <a @class(['uh-admin-row-main', 'uh-link-quiet', 'font-semibold' => $lead->is_unread, 'font-medium' => ! $lead->is_unread]) href="{{ route('admin.leads.show', $lead) }}">{{ $lead->name }}</a>
                                    @if($lead->is_unread)<span class="sr-only">(unread)</span><span class="uh-admin-unread-dot ml-1 inline-block size-1.5 rounded-full" aria-hidden="true"></span>@endif
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
                                <td class="uh-admin-row-actions">
                                    <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.leads.show', $lead) }}">Open</a>
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
        <x-ui.empty icon="inbox"
                    :title="$hasFilters ? 'No leads match this filter' : 'No leads yet'"
                    :description="$hasFilters ? 'Try a wider date range or clear some filters.' : 'Enquiries and visit requests submitted on the public site appear here immediately.'">
            @if($hasFilters)
                <a class="uh-btn-primary uh-btn-sm" href="{{ route('admin.leads.index') }}">Clear filter</a>
            @endif
        </x-ui.empty>
    @endif
@endsection
