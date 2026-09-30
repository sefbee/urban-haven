@extends('layouts.admin')
@section('title', 'Site visits')

@section('content')
    <x-ui.page-header compact title="Site visits"
                      description="Viewing requests from the public site. Confirm the slot by phone before the visitor travels." />

    @if($visits->isNotEmpty())
        <div class="uh-panel-flush mt-6 overflow-hidden">
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
                                    {{ $visit->preferred_at ? \App\Support\DisplayTimezone::format($visit->preferred_at) : 'No time given' }}
                                </td>
                                <td><x-ui.status :status="$visit->status" /></td>
                                <td>
                                    @php($allowed = \App\Models\SiteVisitRequest::TRANSITIONS[$visit->status] ?? [])
                                    @if($allowed !== [])
                                        <form method="POST" action="{{ route('admin.visits.status', $visit) }}" class="flex items-center gap-2">
                                            @csrf
                                            <select name="status" class="uh-select min-h-9 w-auto py-1.5 text-[0.8125rem]"
                                                    aria-label="New status for {{ $visit->lead?->name ?? 'this visit' }}">
                                                @foreach($allowed as $status)
                                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="uh-btn-outline uh-btn-sm">Save</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-[var(--color-muted)]">No further changes</span>
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
        <x-ui.empty class="mt-6" icon="calendar" title="No visit requests yet"
                    description="When someone books a viewing from a property page, the request lands here." />
    @endif
@endsection
