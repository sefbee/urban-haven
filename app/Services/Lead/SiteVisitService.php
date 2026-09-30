<?php

namespace App\Services\Lead;

use App\Contracts\LeadService;
use App\Models\Role;
use App\Models\SiteVisitRequest;
use App\Models\User;
use App\Notifications\SiteVisitNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SiteVisitService
{
    public function __construct(private readonly LeadService $leads) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public function create(array $validated, Request $request): SiteVisitRequest
    {
        $lead = $this->leads->capture([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'property_id' => $validated['property_id'] ?? null,
            'project_id' => $validated['project_id'] ?? null,
            'message' => $validated['notes'] ?? $validated['message'] ?? null,
            'source' => 'visit_request',
        ], $request);

        $visit = DB::transaction(function () use ($validated, $lead) {
            return SiteVisitRequest::query()->create([
                'lead_id' => $lead->id,
                'property_id' => $validated['property_id'] ?? $lead->property_id,
                'project_id' => $validated['project_id'] ?? $lead->project_id,
                'preferred_at' => $validated['preferred_at'] ?? null,
                'status' => SiteVisitRequest::PENDING,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->whereIn('key', [Role::SALES_USER, Role::OWNER_ADMIN]))
            ->get()
            ->each(fn (User $user) => $user->notify(new SiteVisitNotification($visit)));

        return $visit;
    }

    public function updateStatus(SiteVisitRequest $visit, string $status, User $actor): void
    {
        $allowed = SiteVisitRequest::TRANSITIONS[$visit->status] ?? [];

        if (! in_array($status, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => "Cannot move a {$visit->status} visit to {$status}.",
            ]);
        }

        $visit->forceFill(['status' => $status])->save();
    }
}
