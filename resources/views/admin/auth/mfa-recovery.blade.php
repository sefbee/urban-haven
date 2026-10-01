@extends('layouts.auth')
@section('title', 'Recovery codes')

@section('card')
    <h1 class="uh-h3">Save your recovery codes</h1>
    <p class="mt-1.5 text-sm text-[var(--color-muted)]">
        Each code works once if you lose your phone. Store them somewhere safe — they will not be shown again.
    </p>

    <ul class="mt-5 grid grid-cols-2 gap-2 rounded-xl bg-[var(--color-surface-2)] p-4 font-mono text-sm text-ink" dir="ltr">
        @foreach($codes as $code)
            <li class="select-all">{{ $code }}</li>
        @endforeach
    </ul>

    <a href="{{ route('admin.dashboard') }}" class="uh-btn-primary uh-btn-block mt-6">I have saved my codes</a>
@endsection
