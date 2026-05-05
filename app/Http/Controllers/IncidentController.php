<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Http\Request;

class IncidentController extends Controller
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
            'creator',
            'assignee',
            'attachments',
            'statusHistories'
        ])->findOrFail($id);

        return response()->json($incident);
    }

    // ✅ POST /api/incidents
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'in:draft,reviewed,published',
            'priority' => 'in:low,medium,high,critical',
            'source' => 'in:email,telegram,teams,manual,rpa',
            'category' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'created_by' => 'required|exists:users,id',
        ]);

        $incident = Incident::create($validated);

        return response()->json($incident, 201);
    }

    // ✅ PUT /api/incidents/{id}
    public function update(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string',
            'description' => 'nullable|string',
            'status' => 'in:draft,reviewed,published',
            'priority' => 'in:low,medium,high,critical',
            'source' => 'in:email,telegram,teams,manual,rpa',
            'category' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'updated_by' => 'nullable|exists:users,id',
        ]);

        $incident->update($validated);

        return response()->json($incident);
    }

    // ✅ DELETE /api/incidents/{id}
    public function destroy($id)
    {
        $incident = Incident::findOrFail($id);
        $incident->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}