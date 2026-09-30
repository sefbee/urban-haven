<p>Hello,</p>
<p>A new enquiry arrived from {{ $lead->name }} ({{ $lead->phone }}).</p>
<p>Property: {{ $lead->property?->title ?? 'General' }}</p>
<p>{{ $lead->message }}</p>
