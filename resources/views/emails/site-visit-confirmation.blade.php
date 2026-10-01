<p>Hello,</p>
<p>A site visit was requested by {{ $visit->lead->name ?? 'a visitor' }}.</p>
<p>Preferred time: {{ $visit->preferred_at ? \App\Support\DisplayTimezone::format($visit->preferred_at, 'j M Y, g:i a') : 'Not specified' }} (Asia/Dhaka)</p>
<p>The request is not confirmed until a staff member confirms it. <a href="{{ route('admin.visits.index') }}">Open site visits</a>.</p>
