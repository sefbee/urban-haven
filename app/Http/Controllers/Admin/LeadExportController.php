<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Support\DisplayTimezone;
use Illuminate\Http\Request;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadExportController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', Lead::class);

        $query = Lead::query()->with(['property', 'project', 'assignee'])->orderBy('id');
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->date('to'));
        }

        $headers = ['name', 'phone', 'email', 'property_title', 'project_name', 'source', 'utm_source', 'utm_medium', 'utm_campaign', 'status', 'assigned_to', 'created_at'];

        return response()->streamDownload(function () use ($query, $headers): void {
            $csv = Writer::fromString();
            $csv->insertOne($headers);
            $query->each(function (Lead $lead) use ($csv): void {
                $csv->insertOne([
                    $lead->name,
                    $lead->phone,
                    $lead->email,
                    $lead->property?->title,
                    $lead->project?->name,
                    $lead->source,
                    $lead->utm_source,
                    $lead->utm_medium,
                    $lead->utm_campaign,
                    $lead->status,
                    $lead->assignee?->name,
                    DisplayTimezone::format($lead->created_at, 'Y-m-d H:i'),
                ]);
            });
            echo $csv->toString();
        }, 'leads.csv', ['Content-Type' => 'text/csv']);
    }
}
