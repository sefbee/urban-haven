@extends('layouts.admin')
@section('title', 'Leads')

@php($hasFilters = collect($filters)->filter()->isNotEmpty())

@section('content')
    <x-ui.page-header compact title="Leads"
                      description="Every enquiry captured from the public site, newest first." />

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        {{-- Filters --}}
        <form method="GET" class="uh-panel lg:col-span-2">
            <h2 class="uh-h4">Filter</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <x-ui.select name="status" label="Status">
                    <option value="">Any status</option>
                    @foreach(\App\Models\Lead::STATUSES as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="utm_source" label="Campaign source" :value="$filters['utm_source'] ?? ''" placeholder="google" />
                <x-ui.input name="utm_campaign" label="Campaign name" :value="$filters['utm_campaign'] ?? ''" placeholder="eid-offer" />
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <button type="submit" class="uh-btn-primary uh-btn-sm">Apply filter</button>
                @if($hasFilters)
                    <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.leads.index') }}">Clear</a>
                @endif
            </div>
        </form>

        {{-- Export --}}
        <form method="GET" action="{{ route('admin.leads.export') }}" class="uh-panel">
            <h2 class="uh-h4">Export</h2>
            <p class="mt-1 text-xs text-[var(--color-muted)]">Download leads received between two dates as CSV.</p>
            <div class="mt-4 space-y-3">
                <x-ui.input name="from" label="From" type="date" />
                <x-ui.input name="to" label="To" type="date" />
            </div>
            <button type="submit" class="uh-btn-outline uh-btn-sm uh-btn-block mt-4">
                <x-icon name="download" class="size-4" />
                Export CSV
            </button>
        </form>
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
                            <th scope="col">Status</th>
                            <th scope="col">Priority</th>
                            <th scope="col">Source</th>
                            <th scope="col">Assigned</th>
                            <th scope="col">Received</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leads as $lead)
                            <tr>
                                <td>
                                    <a class="uh-link-quiet font-medium" href="{{ route('admin.leads.show', $lead) }}">{{ $lead->name }}</a>
                                    <span class="mt-0.5 block text-xs text-[var(--color-muted)]" dir="ltr">{{ $lead->phone }}</span>
                                </td>
                                <td class="min-w-40 max-w-52 truncate">
                                    {{ $lead->property?->title ?? $lead->project?->name ?? '—' }}
                                </td>
                                <td><x-ui.status :status="$lead->status" /></td>
                                <td><x-ui.status :status="$lead->priority" /></td>
                                <td class="whitespace-nowrap text-xs">
                                    {{ $lead->utm_source ?? $lead->source ?? 'Direct' }}
                                </td>
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

        @if($leads->hasPages())
            <div class="mt-6">{{ $leads->links() }}</div>
        @endif
    @else
        <x-ui.empty class="mt-7" icon="inbox"
                    :title="$hasFilters ? 'No leads match this filter' : 'No leads yet'"
                    :description="$hasFilters ? 'Try a wider date range or clear the campaign fields.' : 'Enquiries and visit requests submitted on the public site appear here immediately.'">
            @if($hasFilters)
                <a class="uh-btn-primary uh-btn-sm" href="{{ route('admin.leads.index') }}">Clear filter</a>
            @endif
        </x-ui.empty>
    @endif
@endsection
