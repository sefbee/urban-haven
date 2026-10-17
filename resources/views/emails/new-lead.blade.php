<p>Hello,</p>
<p>{{ $lead->is_repeat_contact ? 'A repeat inquiry' : 'A new inquiry' }} ({{ $lead->typeLabel() }}) arrived from {{ $lead->name }}.</p>
<p>Contact: {{ $lead->name }} · {{ $lead->phone }}{{ $lead->email ? ' · '.$lead->email : '' }}</p>
<p>Regarding: {{ $lead->property?->title ?? $lead->project?->name ?? 'General contact' }}</p>
@if($lead->property_type || $lead->property_location || $lead->number_of_units || $lead->current_status || $lead->service_type || $lead->preferred_contact_time)
    <dl>
        <dt>Property type</dt><dd>{{ $lead->property_type ? ucfirst(str_replace('_', ' ', $lead->property_type)) : 'Not stated' }}</dd>
        <dt>Property location</dt><dd>{{ $lead->property_location ?: 'Not stated' }}</dd>
        <dt>Number of units</dt><dd>{{ $lead->number_of_units ?? 'Not stated' }}</dd>
        <dt>Current status</dt><dd>{{ $lead->current_status ? ucfirst(str_replace('_', ' ', $lead->current_status)) : 'Not stated' }}</dd>
        <dt>Requested service</dt><dd>{{ $lead->service_type ? ucfirst(str_replace('_', ' ', $lead->service_type)) : 'Not stated' }}</dd>
        <dt>Preferred contact</dt><dd>{{ $lead->preferred_contact ? ucfirst($lead->preferred_contact) : 'Not stated' }}</dd>
        <dt>Best time to contact</dt><dd>{{ $lead->preferred_contact_time ? ucfirst($lead->preferred_contact_time) : 'Not stated' }}</dd>
    </dl>
@endif
@if(filled($lead->message))<p>Details: {{ $lead->message }}</p>@endif
<p><a href="{{ route('admin.leads.show', $lead) }}">Open lead #{{ $lead->id }} in the admin</a> to see contact details and respond.</p>
