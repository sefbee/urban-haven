@extends('layouts.admin')
@section('title', $lead->name)

@php
    $tel = \App\Support\PhoneNumber::telHref($lead->phone);
    $whatsapp = \App\Support\PhoneNumber::whatsappHref($lead->phone);
    $canUpdate = auth()->user()->can('update', $lead);
    $openFollowUps = $lead->followUps->where('status', \App\Models\LeadFollowUp::OPEN)->sortBy('scheduled_at');
    $closedFollowUps = $lead->followUps->where('status', '!=', \App\Models\LeadFollowUp::OPEN)->sortByDesc('scheduled_at');
    $activityLabels = [
        'created' => 'Lead received',
        'note' => 'Note added',
        'stage_change' => 'Stage changed',
        'assignment' => 'Assigned',
        'follow_up' => 'Follow-up',
        'visit_outcome' => 'Visit updated',
        'priority' => 'Priority changed',
        'export' => 'Included in an export',
        'opened' => 'Opened',
    ];
@endphp

@section('content')
    <x-ui.page-header compact :title="$lead->name">
        <x-slot:eyebrow>Communication · {{ $lead->typeLabel() }} · #{{ $lead->id }}</x-slot:eyebrow>
        <x-slot:actions>
            <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.leads.index') }}">
                <x-icon name="chevron-left" class="size-4" />
                All leads
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.admin-related label="Connected to">
        <a href="{{ route('admin.follow-ups.index') }}">Follow-ups</a>
        <a href="{{ route('admin.visits.index') }}">Site visits</a>
        @if($lead->property)
            <a href="{{ route('admin.properties.edit', $lead->property) }}">Listing</a>
        @endif
        @if($lead->project)
            <a href="{{ route('admin.projects.edit', $lead->project) }}">Project</a>
        @endif
    </x-ui.admin-related>

    <div class="flex flex-wrap items-center gap-2">
        <x-ui.status :status="$lead->status" />
        <x-ui.status :status="$lead->priority" />
        @if($lead->is_repeat_contact)
            <x-ui.badge tone="warn">Repeat contact</x-ui.badge>
        @endif
        @if($lead->isOverdue())
            <x-ui.badge tone="danger">Follow-up overdue</x-ui.badge>
        @endif
        @if($tel)
            <a class="uh-btn-outline uh-btn-sm" href="{{ $tel }}" dir="ltr">
                <x-icon name="phone" class="size-3.5" />
                {{ $lead->phone }}
            </a>
        @else
            <span class="text-sm" dir="ltr">{{ $lead->phone }}</span>
        @endif
        @if($whatsapp)
            <a class="uh-btn-outline uh-btn-sm" href="{{ $whatsapp }}" target="_blank" rel="noopener">
                <x-icon name="whatsapp" class="size-3.5" />
                WhatsApp
            </a>
        @endif
        @if($lead->email)
            <a class="uh-btn-outline uh-btn-sm" href="mailto:{{ $lead->email }}" dir="ltr">
                <x-icon name="mail" class="size-3.5" />
                {{ $lead->email }}
            </a>
        @endif
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="uh-panel" aria-labelledby="enquiry-heading">
                <h2 id="enquiry-heading" class="uh-h4">Enquiry</h2>

                <dl class="mt-4 grid gap-x-8 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="uh-spec-label">Interested in</dt>
                        <dd class="mt-1 text-sm">
                            @if($lead->property)
                                <a class="uh-link" href="{{ route('admin.properties.edit', $lead->property) }}">{{ $lead->property->title }}</a>
                                @if($lead->property->reference)<span class="text-xs text-[var(--color-muted)]"> · {{ $lead->property->reference }}</span>@endif
                            @elseif($lead->project)
                                <a class="uh-link" href="{{ route('admin.projects.edit', $lead->project) }}">{{ $lead->project->name }}</a>
                            @else
                                General enquiry
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="uh-spec-label">Preferred contact</dt>
                        <dd class="mt-1 text-sm">{{ $lead->preferred_contact ? ucfirst($lead->preferred_contact) : 'Not stated' }}</dd>
                    </div>
                    <div>
                        <dt class="uh-spec-label">Received</dt>
                        <dd class="mt-1 text-sm">{{ \App\Support\DisplayTimezone::format($lead->created_at) }}</dd>
                    </div>
                    <div>
                        <dt class="uh-spec-label">Assigned to</dt>
                        <dd class="mt-1 text-sm">{{ $lead->assignee?->name ?? 'Unassigned' }}</dd>
                    </div>
                    <div>
                        <dt class="uh-spec-label">Source</dt>
                        <dd class="mt-1 text-sm">
                            @if($lead->hasAttribution())
                                {{ collect([$lead->utm_source, $lead->utm_medium, $lead->utm_campaign])->filter()->join(' · ') ?: $lead->source }}
                            @else
                                Direct or unknown
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="uh-spec-label">Landing page</dt>
                        <dd class="mt-1 break-all text-sm" dir="ltr">{{ $lead->landing_url ?: '—' }}</dd>
                    </div>
                    @if($lead->next_action || $lead->next_action_at)
                        <div class="sm:col-span-2">
                            <dt class="uh-spec-label">Next action</dt>
                            <dd class="mt-1 text-sm">
                                {{ $lead->next_action ?: 'Follow up' }}
                                @if($lead->next_action_at) · {{ \App\Support\DisplayTimezone::format($lead->next_action_at) }} @endif
                            </dd>
                        </div>
                    @endif
                    @if($lead->status === 'lost' && $lead->loss_reason)
                        <div class="sm:col-span-2">
                            <dt class="uh-spec-label">Loss reason</dt>
                            <dd class="mt-1 text-sm">{{ \App\Models\Lead::LOSS_REASONS[$lead->loss_reason] ?? $lead->loss_reason }}</dd>
                        </div>
                    @endif
                </dl>

                @if(filled($lead->message))
                    <div class="mt-5 border-t border-line pt-5">
                        <p class="uh-spec-label">Their message</p>
                        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed">{{ $lead->message }}</p>
                    </div>
                @endif

                @if($lead->siteVisits->isNotEmpty())
                    <div class="mt-5 border-t border-line pt-5">
                        <p class="uh-spec-label">Site visits</p>
                        <ul class="mt-2 space-y-2 text-sm">
                            @foreach($lead->siteVisits as $visit)
                                <li class="flex flex-wrap items-center gap-2">
                                    <x-ui.status :status="$visit->status" />
                                    <span>{{ $visit->confirmed_at ? 'Confirmed for '.\App\Support\DisplayTimezone::format($visit->confirmed_at) : ($visit->preferred_at ? 'Requested '.\App\Support\DisplayTimezone::format($visit->preferred_at) : 'No time given') }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <a class="uh-link mt-2 inline-block text-xs" href="{{ route('admin.visits.index') }}">Manage visits</a>
                    </div>
                @endif
            </section>

            @if($history->isNotEmpty() || $lead->repeatOf)
                <section class="uh-panel" aria-labelledby="history-heading">
                    <h2 id="history-heading" class="uh-h4">Earlier contacts from this number</h2>
                    <ul class="mt-3 divide-y divide-[var(--color-line)] text-sm">
                        @foreach($history as $earlier)
                            <li class="flex flex-wrap items-center justify-between gap-2 py-2.5">
                                <a class="uh-link" href="{{ route('admin.leads.show', $earlier) }}">
                                    #{{ $earlier->id }} · {{ $earlier->property?->title ?? $earlier->project?->name ?? $earlier->typeLabel() }}
                                </a>
                                <span class="flex items-center gap-2 text-xs text-[var(--color-muted)]">
                                    <x-ui.status :status="$earlier->status" />
                                    {{ \App\Support\DisplayTimezone::format($earlier->created_at) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                    @if($history->isEmpty())
                        <p class="mt-3 text-xs text-[var(--color-muted)]">An earlier lead exists but is assigned to someone else.</p>
                    @endif
                </section>
            @endif

            <section class="uh-panel" aria-labelledby="notes-heading">
                <h2 id="notes-heading" class="uh-h4">Notes</h2>

                @if($canUpdate)
                    <form method="POST" action="{{ route('admin.leads.notes', $lead) }}" class="mt-4 space-y-3">
                        @csrf
                        <x-ui.textarea name="body" label="Add a note" rows="3" required maxlength="5000"
                                       placeholder="What was discussed, and what happens next." />
                        <button type="submit" class="uh-btn-primary uh-btn-sm">Add note</button>
                    </form>
                @endif

                @if($lead->notes->isNotEmpty())
                    <ul class="mt-5 space-y-3 border-t border-line pt-5">
                        @foreach($lead->notes->sortByDesc('created_at') as $note)
                            <li class="uh-admin-note rounded-lg px-4 py-3">
                                <p class="whitespace-pre-line text-sm leading-relaxed">{{ $note->body }}</p>
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

            <section class="uh-panel" aria-labelledby="timeline-heading">
                <h2 id="timeline-heading" class="uh-h4">Activity</h2>
                @if($lead->activities->isNotEmpty())
                    <ol class="mt-4 space-y-3 border-l border-line pl-4 text-sm">
                        @foreach($lead->activities->sortByDesc('created_at') as $activity)
                            @php($payload = $activity->payload ?? [])
                            <li>
                                <p class="font-medium">
                                    {{ $activityLabels[$activity->type] ?? ucfirst(str_replace('_', ' ', $activity->type)) }}
                                    @if($activity->type === 'stage_change')
                                        <span class="font-normal">— {{ \App\Models\Lead::STATUS_LABELS[$payload['from'] ?? ''] ?? '—' }} to {{ \App\Models\Lead::STATUS_LABELS[$payload['to'] ?? ''] ?? '—' }}</span>
                                        @if(! empty($payload['loss_reason']))
                                            <span class="font-normal">({{ \App\Models\Lead::LOSS_REASONS[$payload['loss_reason']] ?? $payload['loss_reason'] }})</span>
                                        @endif
                                    @elseif($activity->type === 'created' && ! empty($payload['repeat']))
                                        <span class="font-normal">— repeat contact</span>
                                    @endif
                                </p>
                                <p class="text-xs text-[var(--color-muted)]">{{ $activity->actor?->name ?? 'System' }} · {{ \App\Support\DisplayTimezone::format($activity->created_at) }}</p>
                            </li>
                        @endforeach
                    </ol>
                @else
                    <p class="mt-3 text-sm text-[var(--color-muted)]">No activity recorded yet.</p>
                @endif
            </section>
        </div>

        <div class="space-y-6">
            @if($canUpdate)
                <section class="uh-panel" aria-labelledby="stage-heading" x-data="{ stage: @js(old('status', $lead->status)) }">
                    <h2 id="stage-heading" class="uh-h4">Stage</h2>
                    <form method="POST" action="{{ route('admin.leads.status', $lead) }}" class="mt-4 space-y-3">
                        @csrf
                        <x-ui.select name="status" label="Stage" x-model="stage">
                            @foreach(\App\Models\Lead::STATUS_LABELS as $value => $label)
                                <option value="{{ $value }}" @selected($lead->status === $value)>{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                        <div x-show="stage === 'lost'" x-cloak>
                            <x-ui.select name="loss_reason" label="Why was it lost?" x-bind:required="stage === 'lost'">
                                <option value="">Choose a reason</option>
                                @foreach(\App\Models\Lead::LOSS_REASONS as $value => $label)
                                    <option value="{{ $value }}" @selected(old('loss_reason', $lead->loss_reason) === $value)>{{ $label }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>
                        <button type="submit" class="uh-btn-primary uh-btn-sm uh-btn-block">Save stage</button>
                    </form>
                </section>

                <section class="uh-panel" aria-labelledby="priority-heading">
                    <h2 id="priority-heading" class="uh-h4">Priority and next action</h2>
                    <form method="POST" action="{{ route('admin.leads.priority', $lead) }}" class="mt-4 space-y-3">
                        @csrf
                        <x-ui.select name="priority" label="Priority">
                            @foreach(\App\Models\Lead::PRIORITIES as $priority)
                                <option value="{{ $priority }}" @selected($lead->priority === $priority)>{{ ucfirst($priority) }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.input name="next_action" label="Next action" :value="$lead->next_action" optional maxlength="255" />
                        <x-ui.input name="next_action_at" label="Due" type="datetime-local" optional
                                    :value="$lead->next_action_at?->timezone(config('urbanhaven.display_timezone'))->format('Y-m-d\TH:i')" />
                        <button type="submit" class="uh-btn-outline uh-btn-sm uh-btn-block">Save</button>
                    </form>
                </section>
            @endif

            @can('assign', $lead)
                <section class="uh-panel" aria-labelledby="assign-heading">
                    <h2 id="assign-heading" class="uh-h4">Assignment</h2>
                    <form method="POST" action="{{ route('admin.leads.assign', $lead) }}" class="mt-4 space-y-3">
                        @csrf
                        <x-ui.select name="assigned_to" label="Assigned to">
                            <option value="">Choose a team member</option>
                            @foreach($salesUsers as $user)
                                <option value="{{ $user->id }}" @selected($lead->assigned_to === $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </x-ui.select>
                        <button type="submit" class="uh-btn-outline uh-btn-sm uh-btn-block">Assign</button>
                    </form>
                </section>
            @endcan

            <section class="uh-panel" aria-labelledby="follow-up-heading">
                <h2 id="follow-up-heading" class="uh-h4">Follow-ups</h2>

                @if($canUpdate)
                    <form method="POST" action="{{ route('admin.leads.follow-ups', $lead) }}" class="mt-3">
                        @csrf
                        <input type="hidden" name="quick" value="call_tomorrow">
                        <button type="submit" class="uh-btn-primary uh-btn-sm uh-btn-block">
                            <x-icon name="phone" class="size-3.5" />
                            Call tomorrow at 10:00
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.leads.follow-ups', $lead) }}" class="mt-4 space-y-3 border-t border-line pt-4">
                        @csrf
                        <x-ui.select name="action_type" label="Action">
                            @foreach(\App\Models\LeadFollowUp::ACTION_TYPES as $type)
                                <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.input name="scheduled_at" label="When" type="datetime-local" required />
                        <x-ui.input name="notes" label="Details" optional maxlength="2000" />
                        <button type="submit" class="uh-btn-outline uh-btn-sm uh-btn-block">Schedule</button>
                    </form>
                @endif

                @if($openFollowUps->isNotEmpty() || $closedFollowUps->isNotEmpty())
                    <ul class="mt-4 divide-y divide-[var(--color-line)] border-t border-line">
                        @foreach($openFollowUps->concat($closedFollowUps) as $followUp)
                            <li class="py-3">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="text-sm font-medium">{{ ucfirst(str_replace('_', ' ', $followUp->action_type)) }}</p>
                                    <x-ui.status :status="$followUp->status" />
                                </div>
                                <p class="mt-0.5 text-xs {{ $followUp->status === 'open' && $followUp->scheduled_at->isPast() ? 'font-semibold text-[var(--color-danger)]' : 'text-[var(--color-muted)]' }}">
                                    {{ \App\Support\DisplayTimezone::format($followUp->scheduled_at) }}
                                    @if($followUp->user) · {{ $followUp->user->name }} @endif
                                </p>
                                @if($followUp->notes)
                                    <p class="mt-1 text-xs">{{ $followUp->notes }}</p>
                                @endif
                                @if($canUpdate && $followUp->status === 'open')
                                    <div class="mt-2 flex gap-2">
                                        <form method="POST" action="{{ route('admin.follow-ups.complete', $followUp) }}">
                                            @csrf
                                            <button type="submit" class="uh-btn-outline uh-btn-sm">Done</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.follow-ups.cancel', $followUp) }}">
                                            @csrf
                                            <button type="submit" class="uh-btn-ghost uh-btn-sm">Cancel</button>
                                        </form>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-4 text-sm text-[var(--color-muted)]">Nothing scheduled.</p>
                @endif
            </section>
        </div>
    </div>
@endsection
