<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Http\Request;
use App\Services\IncidentService;


class IncidentController
{
    // ✅ GET /api/incidents
    public function index()
    {
        return response()->json(
            Incident::with(['creator', 'assignee'])->latest()->get()
        );
    }

    // ✅ GET /api/incidents/{id}
    public function show($id)
    {
        $incident = Incident::with([
            'histories',
            'rpaLogs',
            'attachments',
            'aiProcessing'
        ])->findOrFail($id);

        return response()->json([
            'id' => $incident->id,
            'title' => $incident->title,
            'description' => $incident->description,
            'status' => $incident->status,
            'priority' => $incident->priority,
            'category' => $incident->category,
            'source' => $incident->source,
            'assigned_to' => $incident->assigned_to,

            'histories' => $incident->histories,
            'rpa_logs' => $incident->rpaLogs,
            'attachments' => $incident->attachments,
            'ai_processing' => $incident->aiProcessing,
        ]);
    }

    // ✅ POST /api/incidents
    public function store(Request $request, IncidentService $service)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'attachments.*' => 'file|mimes:jpg,png,pdf,webp,docx|max:5120',
        ]);

        $incident = $service->create($request, $request->user()->id);

        return response()->json($incident);
    }

    // ✅ PUT /api/incidents/{id}
    public function update(
        Request $request,
        $id,
        IncidentService $service
    ) {
        $request->validate([
            'title' => 'sometimes|string',
            'description' => 'nullable|string',
            'attachments.*' => 'file|mimes:jpg,png,pdf,webp,docx|max:5120',

            'status' => 'in:draft,reviewed,published,rejected',
            'priority' => 'in:low,medium,high',
            'source' => 'in:email,telegram,teams,manual,rpa',
            'category' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $incident = $service->update(
            $request,
            $id,
            $request->user()->id
        );

        return response()->json($incident);
    }

    // ✅ DELETE /api/incidents/{id}
    public function destroy($id, IncidentService $service)
    {
        $incident = Incident::findOrFail($id);
        $service->delete($id);

        return response()->json([
            'message' => 'Incident deleted successfully'
        ]);
    }

    // Reviewer update (status, assign, add notes)
    public function reviewerUpdate(Request $request, $id, IncidentService $service)
    {
        $request->validate([
            'status' => 'in:draft,reviewed,published,rejected',
            'assigned_to' => 'nullable|exists:users,id',
            'note' => 'nullable|string',
        ]);
        $incident = $service->reviewerUpdate(
            $request,
            $id,
            $request->user()->id
        );
        return response()->json($incident);
    }

    // Get Assigned Incidents for Staff
    public function assignedIncidents(Request $request)
    {
        $user = $request->user();

        return response()->json(
            Incident::with(['creator', 'assignee'])
                ->where('assigned_to', $user->id)
                ->latest()
                ->get()
        );
    }

    // Reviewer/Staff update status
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:draft,reviewed,published,rejected'
        ]);

        $incident = Incident::findOrFail($id);

        $incident->update([
            'status' => $request->status,
            'updated_by' => $request->user()->id
        ]);

        // OPTIONAL: log history (if table exists)
        $incident->statusHistories()->create([
            'status' => $request->status,
            'changed_by' => $request->user()->id,
            'note' => 'Status updated via API'
        ]);

        return response()->json([
            'message' => 'Status updated',
            'data' => $incident
        ]);
    }

    public function storeRPA(Request $request, IncidentService $incidentService)
    {
        try {

            // optional validation
            $request->validate([
                'title' => 'required|string',
                'description' => 'nullable|string',
                'type' => 'nullable|in:email,drive,telegram',
                'attachment' => 'nullable|string', // base64 or file path depending on your RPA implementation
            ]);

            // RPA bot user id, run 'php artisan migrate:fresh --seed' to reset database 
            $botUserId = 4;

            $incident = $incidentService->createFromRPA(
                $request,
                $botUserId,
                $request['type'] ?? 'email',
            );

            return response()->json([
                'success' => true,
                'incident' => $incident
            ], 201);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
