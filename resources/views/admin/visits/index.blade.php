@extends('layouts.admin')
@section('title', 'Site visits')

@php
    $activeUpcoming = ($filters['view'] ?? null) === 'upcoming';
    $activeAll = blank($filters['view'] ?? null) && blank($filters['status'] ?? null);
@endphp

@section('content')
    <x-ui.page-header compact title="Site visits"
                      description="Viewing requests from the public site. Confirm the slot by phone before the visitor travels.">
        <x-slot:eyebrow>Communication</x-slot:eyebrow>
    </x-ui.page-header>

    <x-ui.admin-related label="Next to this">
        <a href="{{ route('admin.leads.index') }}">Leads</a>
        <a href="{{ route('admin.follow-ups.index') }}">Follow-ups</a>
    </x-ui.admin-related>

    <x-ui.admin-tabs label="Visit views">
        <a href="{{ route('admin.visits.index', ['view' => 'upcoming']) }}" @class(['is-active' => $activeUpcoming]) @if($activeUpcoming) aria-current="page" @endif>Upcoming</a>
        <a href="{{ route('admin.visits.index') }}" @class(['is-active' => $activeAll]) @if($activeAll) aria-current="page" @endif>All</a>
        @foreach(\App\Models\SiteVisitRequest::STATUS_LABELS as $value => $label)
            <a href="{{ route('admin.visits.index', ['status' => $value]) }}" @class(['is-active' => ($filters['status'] ?? null) === $value]) @if(($filters['status'] ?? null) === $value) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </x-ui.admin-tabs>

    @if($visits->isNotEmpty())
        <div class="uh-panel-flush overflow-hidden">
            <div class="uh-table-scroll">
                <table class="uh-table">
                    <caption class="sr-only">Requested site visits</caption>
                    <thead>
                        <tr>
                            <th scope="col">Visitor</th>
                            <th scope="col">Property</th>
                            <th scope="col">Requested for</th>
                            <th scope="col">Status</th>
                            <th scope="col">Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($visits as $visit)
                            <tr>
                                <td>
                                    @if($visit->lead)
                                        <a class="uh-link-quiet font-medium" href="{{ route('admin.leads.show', $visit->lead) }}">{{ $visit->lead->name }}</a>
                                        <span class="mt-0.5 block text-xs text-[var(--color-muted)]" dir="ltr">{{ $visit->lead->phone }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="min-w-40 max-w-52 truncate">{{ $visit->property?->title ?? $visit->project?->name ?? '—' }}</td>
                                <td class="whitespace-nowrap text-xs">
                                    @if($visit->confirmed_at)
                                        <span class="font-semibold">{{ \App\Support\DisplayTimezone::format($visit->confirmed_at) }}</span>
                                        <span class="block text-[var(--color-muted)]">Confirmed</span>
                                    @else
                                        {{ $visit->preferred_at ? \App\Support\DisplayTimezone::format($visit->preferred_at) : 'No time given' }}
                                        <span class="block text-[var(--color-muted)]">Requested</span>
                                    @endif
                                </td>
                                <td><x-ui.status :status="$visit->status" /></td>
                                <td>
                                    @php($allowed = \App\Models\SiteVisitRequest::TRANSITIONS[$visit->status] ?? [])
                                    @if($allowed !== [])
                                        <form method="POST" action="{{ route('admin.visits.status', $visit) }}" class="min-w-56 space-y-2"
                                              x-data="{ status: @js($allowed[0]) }">
                                            @csrf
                                            <select name="status" x-model="status" class="uh-select min-h-9 py-1.5 text-[0.8125rem]"
                                                    aria-label="New status for {{ $visit->lead?->name ?? 'this visit' }}">
                                                @foreach($allowed as $status)
                                                    <option value="{{ $status }}">{{ \App\Models\SiteVisitRequest::STATUS_LABELS[$status] ?? ucfirst($status) }}</option>
                                                @endforeach
                                            </select>
                                            <template x-if="status === 'confirmed'">
                                                <input type="datetime-local" name="confirmed_at" required class="uh-input min-h-9 py-1.5 text-[0.8125rem]"
                                                       aria-label="Confirmed visit time"
                                                       value="{{ $visit->preferred_at?->timezone(config('urbanhaven.display_timezone'))->format('Y-m-d\TH:i') }}">
                                            </template>
                                            <template x-if="status === 'completed' || status === 'no_show' || status === 'cancelled'">
                                                <textarea name="outcome_note" rows="2" maxlength="2000" class="uh-input py-1.5 text-[0.8125rem]"
                                                          :required="status === 'completed'" aria-label="Outcome note"
                                                          :placeholder="status === 'completed' ? 'What happened at the visit?' : 'Optional note'"></textarea>
                                            </template>
                                            <button type="submit" class="uh-btn-outline uh-btn-sm">Save</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-[var(--color-muted)]">{{ $visit->outcome_note ?: 'No further changes' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($visits->hasPages())
            <div class="mt-6">{{ $visits->links() }}</div>
        @endif
    @else
        <x-ui.empty icon="calendar" title="No visit requests yet"
                    description="When someone books a viewing from a property page, the request lands here." />
    @endif
@endsection
