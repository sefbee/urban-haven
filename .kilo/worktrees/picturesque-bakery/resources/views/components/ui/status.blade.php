@props(['status'])

@php
    $key = strtolower((string) $status);

    $map = [
        // Editorial workflow
        'draft' => ['tone' => 'outline', 'label' => 'Draft'],
        'pending_review' => ['tone' => 'warn', 'label' => 'In review'],
        'approved' => ['tone' => 'info', 'label' => 'Approved'],
        'published' => ['tone' => 'success', 'label' => 'Published'],
        'unpublished' => ['tone' => 'danger', 'label' => 'Unpublished'],
        // Inventory availability
        'available' => ['tone' => 'success', 'label' => 'Available'],
        'reserved' => ['tone' => 'warn', 'label' => 'Reserved'],
        'sold' => ['tone' => 'danger', 'label' => 'Sold'],
        'rented' => ['tone' => 'danger', 'label' => 'Rented'],
        'off-market' => ['tone' => 'outline', 'label' => 'Off market'],
        // Leads
        'new' => ['tone' => 'info', 'label' => 'New'],
        'contacted' => ['tone' => 'warn', 'label' => 'Contacted'],
        'qualified' => ['tone' => 'info', 'label' => 'Qualified'],
        'visit_scheduled' => ['tone' => 'warn', 'label' => 'Visit scheduled'],
        'negotiation' => ['tone' => 'warn', 'label' => 'Negotiating'],
        'negotiating' => ['tone' => 'warn', 'label' => 'Negotiating'],
        'won' => ['tone' => 'success', 'label' => 'Won'],
        'lost' => ['tone' => 'danger', 'label' => 'Lost'],
        // Visits & priority
        'requested' => ['tone' => 'info', 'label' => 'Requested'],
        'pending' => ['tone' => 'warn', 'label' => 'Awaiting confirmation'],
        'confirmed' => ['tone' => 'success', 'label' => 'Confirmed'],
        'completed' => ['tone' => 'success', 'label' => 'Completed'],
        'cancelled' => ['tone' => 'danger', 'label' => 'Cancelled'],
        'urgent' => ['tone' => 'danger', 'label' => 'Urgent'],
        'high' => ['tone' => 'warn', 'label' => 'High'],
        'normal' => ['tone' => 'outline', 'label' => 'Normal'],
        'low' => ['tone' => 'outline', 'label' => 'Low'],
        // Projects
        'upcoming' => ['tone' => 'info', 'label' => 'Upcoming'],
        'ongoing' => ['tone' => 'warn', 'label' => 'Ongoing'],
    ];

    $resolved = $map[$key] ?? ['tone' => 'neutral', 'label' => ucfirst(str_replace('_', ' ', $key))];
@endphp

<x-ui.badge :tone="$resolved['tone']" {{ $attributes }}>{{ __($resolved['label']) }}</x-ui.badge>
