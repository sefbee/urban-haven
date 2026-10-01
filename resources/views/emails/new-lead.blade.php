<p>Hello,</p>
<p>{{ $lead->is_repeat_contact ? 'A repeat inquiry' : 'A new inquiry' }} ({{ $lead->typeLabel() }}) arrived from {{ $lead->name }}.</p>
<p>Regarding: {{ $lead->property?->title ?? $lead->project?->name ?? 'General contact' }}</p>
<p><a href="{{ route('admin.leads.show', $lead) }}">Open lead #{{ $lead->id }} in the admin</a> to see contact details and respond.</p>
