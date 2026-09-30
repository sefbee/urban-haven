@extends('layouts.admin')
@section('title', $lead->name)

@section('content')
    <x-ui.page-header compact :title="$lead->name">
        <x-slot:eyebrow>Lead</x-slot:eyebrow>
        <x-slot:actions>
            <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.leads.index') }}">
                <x-icon name="chevron-left" class="size-4" />
                All leads
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mt-4 flex flex-wrap items-center gap-2">
        <x-ui.status :status="$lead->status" />
        <x-ui.status :status="$lead->priority" />
        <a class="uh-btn-outline uh-btn-sm" href="tel:{{ preg_replace('/\s+/', '', $lead->phone) }}" dir="ltr">
            <x-icon name="phone" class="size-3.5" />
            {{ $lead->phone }}
        </a>
        @if($lead->email)
            <a class="uh-btn-outline uh-btn-sm" href="mailto:{{ $lead->email }}" dir="ltr">
                <x-icon name="mail" class="size-3.5" />
                {{ $lead->email }}
            </a>
        @endif
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        {{-- Enquiry detail --}}
        <section class="uh-panel lg:col-span-2">
            <h2 class="uh-h4">Enquiry</h2>

            <dl class="mt-4 grid gap-x-8 gap-y-4 sm:grid-cols-2">
                <div>
                    <dt class="uh-spec-label">Interested in</dt>
                    <dd class="mt-1 text-sm">
                        @if($lead->property)
                            <a class="uh-link" href="{{ route('admin.properties.edit', $lead->property) }}">{{ $lead->property->title }}</a>
                        @elseif($lead->project)
                            <a class="uh-link" href="{{ route('admin.projects.edit', $lead->project) }}">{{ $lead->project->name }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="uh-spec-label">Preferred contact</dt>
                    <dd class="mt-1 text-sm">{{ $lead->preferred_contact ? ucfirst($lead->preferred_contact) : '—' }}</dd>
                </div>
                <div>
                    <dt class="uh-spec-label">Received</dt>
                    <dd class="mt-1 text-sm">{{ \App\Support\DisplayTimezone::format($lead->created_at) }}</dd>
                </div>
                <div>
                    <dt class="uh-spec-label">Attribution</dt>
                    <dd class="mt-1 text-sm">
                        {{ collect([$lead->utm_source, $lead->utm_medium, $lead->utm_campaign])->filter()->join(' · ') ?: ($lead->source ?? 'Direct') }}
                    </dd>
                </div>
            </dl>

            @if(filled($lead->message))
                <div class="mt-5 border-t border-line pt-5">
                    <p class="uh-spec-label">Their message</p>
                    <p class="mt-2 text-sm leading-relaxed">{{ $lead->message }}</p>
                </div>
            @endif

            @if($lead->siteVisits->isNotEmpty())
                <div class="mt-5 border-t border-line pt-5">
                    <p class="uh-spec-label">Site visits</p>
                    <ul class="mt-2 space-y-2 text-sm">
                        @foreach($lead->siteVisits as $visit)
                            <li class="flex flex-wrap items-center gap-2">
                                <x-ui.status :status="$visit->status" />
                                <span>{{ $visit->preferred_at ? \App\Support\DisplayTimezone::format($visit->preferred_at) : 'No time given' }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>

        {{-- Actions --}}
        <div class="space-y-6">
            <section class="uh-panel">
                <h2 class="uh-h4">Update</h2>

                <form method="POST" action="{{ route('admin.leads.status', $lead) }}" class="mt-4 space-y-3">
                    @csrf
                    <x-ui.select name="status" label="Status">
                        @foreach(\App\Models\Lead::STATUSES as $status)
                            <option value="{{ $status }}" @selected($lead->status === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </x-ui.select>
                    <button type="submit" class="uh-btn-primary uh-btn-sm uh-btn-block">Save status</button>
                </form>

                <form method="POST" action="{{ route('admin.leads.assign', $lead) }}" class="mt-5 space-y-3 border-t border-line pt-5">
                    @csrf
                    <x-ui.select name="assigned_to" label="Assigned to">
                        @foreach($salesUsers as $user)
                            <option value="{{ $user->id }}" @selected($lead->assigned_to === $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <button type="submit" class="uh-btn-outline uh-btn-sm uh-btn-block">Assign</button>
                </form>
            </section>

            <section class="uh-panel">
                <h2 class="uh-h4">Schedule a follow-up</h2>
                <form method="POST" action="{{ route('admin.leads.follow-ups', $lead) }}" class="mt-4 space-y-3">
                    @csrf
                    <x-ui.select name="action_type" label="Action">
                        @foreach(\App\Models\LeadFollowUp::ACTION_TYPES as $type)
                            <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input name="scheduled_at" label="When" type="datetime-local" required />
                    <button type="submit" class="uh-btn-outline uh-btn-sm uh-btn-block">Schedule</button>
                </form>
            </section>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Notes --}}
        <section class="uh-panel">
            <h2 class="uh-h4">Notes</h2>

            <form method="POST" action="{{ route('admin.leads.notes', $lead) }}" class="mt-4 space-y-3">
                @csrf
                <x-ui.textarea name="body" label="Add a note" rows="3" required
                               placeholder="What was discussed, and what happens next." />
                <button type="submit" class="uh-btn-primary uh-btn-sm">Add note</button>
            </form>

            @if($lead->notes->isNotEmpty())
                <ul class="mt-5 space-y-3 border-t border-line pt-5">
                    @foreach($lead->notes as $note)
                        <li class="rounded-lg bg-sand px-4 py-3">
                            <p class="text-sm leading-relaxed">{{ $note->body }}</p>
                            <p class="mt-1.5 text-xs text-[var(--color-muted)]">
                                {{ $note->user?->name ?? 'Staff' }} · {{ \App\Support\DisplayTimezone::format($note->created_at) }}
                            </p>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-5 border-t border-line pt-5 text-sm text-[var(--color-muted)]">No notes on this lead yet.</p>
            @endif
        </section>

        {{-- Follow-ups --}}
        <section class="uh-panel">
            <h2 class="uh-h4">Follow-ups</h2>

            @if($lead->followUps->isNotEmpty())
                <ul class="mt-4 divide-y divide-[var(--color-line)]">
                    @foreach($lead->followUps as $followUp)
                        <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium">{{ ucfirst(str_replace('_', ' ', $followUp->action_type)) }}</p>
                                <p class="mt-0.5 text-xs text-[var(--color-muted)]">
                                    {{ \App\Support\DisplayTimezone::format($followUp->scheduled_at) }}
                                    @if($followUp->user) · {{ $followUp->user->name }} @endif
                                </p>
                            </div>
                            @if($followUp->completed_at)
                                <x-ui.badge tone="success">
                                    <x-icon name="check" class="size-3" />
                                    Done
                                </x-ui.badge>
                            @else
                                <form method="POST" action="{{ route('admin.follow-ups.complete', $followUp) }}">
                                    @csrf
                                    <button type="submit" class="uh-btn-outline uh-btn-sm">Mark complete</button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-4 text-sm text-[var(--color-muted)]">Nothing scheduled. Use the form above to plan the next contact.</p>
            @endif
        </section>
    </div>
@endsection
